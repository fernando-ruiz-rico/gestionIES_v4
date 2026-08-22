<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                // FASE 2.1 — modelo fiel a v3: no existe una fila única de "programación".
                // La programación vive en apartados + contenidos asociados a cada materia;
                // se listan las materias que la tienen activa y su estado actual.
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;

                $sql = "SELECT m.id AS id, m.nombre AS materia, c.nombre AS curso,
                               m.horas AS horas,
                               (SELECT COUNT(DISTINCT cp.idApartado)
                                  FROM contenidos_programaciones cp
                                  WHERE cp.idMateria = m.id) AS num_apartados
                            FROM materias m
                            LEFT JOIN cursos c ON c.id = m.idCurso";

                if ($idMateria > 0) {
                    $sql .= " WHERE m.id = $idMateria AND m.tiene_programacion = 1";
                } else {
                    $sql .= " WHERE m.tiene_programacion = 1";
                }

                $sql .= " ORDER BY c.orden, c.nombre, m.nombre";

                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $programaciones = [];
                while ($fila = mysqli_fetch_assoc($result)) {
                    $programaciones[] = $fila;
                }
                mysqli_free_result($result);
                echo json_encode(['success' => true, 'data' => $programaciones]);
            } elseif ($action === 'obtener' && isset($_GET['idMateria'])) {
                // FASE 2.1 — Ver programación (solo lectura): apartados + contenidos
                // de la materia tal y como están guardados en v3.
                $idMateria = intval($_GET['idMateria']);
                if ($idMateria <= 0) {
                    throw new Exception('ID de materia inválido');
                }

                $sql = "SELECT ap.id AS idApartado, ap.titulo, c.texto
                            FROM apartados_programaciones ap
                            JOIN contenidos_programaciones c ON c.idApartado = ap.id AND c.idMateria = $idMateria
                            ORDER BY ap.orden, c.id";
                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }

                // Un apartado puede tener varios contenidos; se agrupan por orden de id.
                $apartados = [];
                $posicion = [];
                while ($fila = mysqli_fetch_assoc($result)) {
                    $idA = $fila['idApartado'];
                    if (!isset($posicion[$idA])) {
                        $posicion[$idA] = count($apartados);
                        $apartados[] = [
                            'idApartado' => $idA,
                            'titulo'     => $fila['titulo'],
                            'texto'      => $fila['texto']
                        ];
                    } else {
                        $apartados[$posicion[$idA]]['texto'] .= "\n" . $fila['texto'];
                    }
                }
                mysqli_free_result($result);

                if (empty($apartados)) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'La materia no tiene programación']);
                } else {
                    echo json_encode(['success' => true, 'data' => $apartados]);
                }
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            // Acción especial: importar programación desde otra materia
            if ($action === 'importar') {
                // Permiso fiel a v3 (importar_programacion.php): solo admin
                checkPermission(array(ROLE_ADMIN));
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);

                if (!isset($data['idMateriaOrigen']) || !isset($data['idMateriaDestino'])) {
                    throw new Exception('Debe especificar materia origen y destino');
                }

                $idMateriaOrigen = intval($data['idMateriaOrigen']);
                $idMateriaDestino = intval($data['idMateriaDestino']);

                if ($idMateriaOrigen <= 0 || $idMateriaDestino <= 0) {
                    throw new Exception('IDs de materia inválidos');
                }

                // Iniciar transacción (mysqli)
                mysqli_begin_transaction($db);

                try {
                    // Borrar contenidos previos de programación destino
                    $sql = "DELETE FROM contenidos_programaciones WHERE idMateria = $idMateriaDestino";
                    mysqli_query($db, $sql);

                    $sql = "DELETE FROM competencias_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = $idMateriaDestino)";
                    mysqli_query($db, $sql);

                    $sql = "DELETE FROM criterios_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = $idMateriaDestino)";
                    mysqli_query($db, $sql);

                    $sql = "DELETE FROM temas WHERE idMateria = $idMateriaDestino";
                    mysqli_query($db, $sql);

                    // Insertar contenidos de la materia origen en la destino
                    $sql = "INSERT INTO contenidos_programaciones(idMateria, idApartado, texto) SELECT $idMateriaDestino AS idMateria, idApartado, texto FROM contenidos_programaciones WHERE idMateria = $idMateriaOrigen";
                    mysqli_query($db, $sql);

                    // Insertar temas
                    $sql = "INSERT INTO temas(idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto) SELECT $idMateriaDestino AS idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto FROM temas WHERE idMateria = $idMateriaOrigen";
                    mysqli_query($db, $sql);

                    // Insertar RA y CE asociados
                    $sql = "SELECT criterios_temas.codigo as CE, temas.orden as tema, resultados_aprendizaje.orden as RA FROM criterios_temas, temas, resultados_aprendizaje WHERE criterios_temas.idRA = resultados_aprendizaje.id AND criterios_temas.idTema = temas.id AND temas.idMateria = $idMateriaOrigen";
                    $result = mysqli_query($db, $sql);
                    
                    while ($fila = mysqli_fetch_assoc($result)) {
                        $codigoCE = mysqli_real_escape_string($db, $fila['CE']);
                        $ordenRA = intval($fila['RA']);
                        $numTema = intval($fila['tema']);

                        // Buscar el id del RA para la materia destino
                        $sql2 = "SELECT id FROM resultados_aprendizaje WHERE idMateria = $idMateriaDestino AND orden = $ordenRA";
                        $result2 = mysqli_query($db, $sql2);
                        $row2 = mysqli_fetch_assoc($result2);
                        $idRA = $row2 ? $row2['id'] : null;
                        mysqli_free_result($result2);

                        // Buscar el id del tema para la materia destino
                        $sql2 = "SELECT id FROM temas WHERE idMateria = $idMateriaDestino AND orden = $numTema";
                        $result2 = mysqli_query($db, $sql2);
                        $row2 = mysqli_fetch_assoc($result2);
                        $idTema = $row2 ? $row2['id'] : null;
                        mysqli_free_result($result2);

                        if ($idRA && $idTema) {
                            $sql2 = "INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES ($idRA, '$codigoCE', $idTema)";
                            mysqli_query($db, $sql2);
                        }
                    }

                    mysqli_commit($db);
                    echo json_encode(['success' => true, 'message' => 'Programación importada correctamente']);

                } catch (Exception $e) {
                    mysqli_rollback($db);
                    throw $e;
                }
            } else {
                // FASE 2.1 — No hay fila única de programación que guardar/actualizar a este nivel.
                // En v3 los datos se editan en apartados (2.2) y contenidos (2.4), no aquí.
                throw new Exception('Las programaciones se gestionan por apartados y contenidos (fase 2.2+). En la fase 2.1 solo es posible listar, ver e importar.');
            }
            break;

        case 'DELETE':
            // FASE 2.1 — No hay fila única que eliminar aquí (en v3 se borrarían sus apartados/contenidos, fase 2.5).
            throw new Exception('Eliminar una programación se gestiona con el borrado de sus apartados y contenidos (fase 2.5).');
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    closeDBConnection($db);
}
?>
