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
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;

                if ($idMateria > 0) {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo
                            FROM programaciones p
                            LEFT JOIN materias m ON p.idMateria = m.id
                            LEFT JOIN grupos g ON p.idGrupo = g.id
                            WHERE p.idMateria = $idMateria
                            ORDER BY p.curso DESC";
                } else {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo
                            FROM programaciones p
                            LEFT JOIN materias m ON p.idMateria = m.id
                            LEFT JOIN grupos g ON p.idGrupo = g.id
                            ORDER BY p.curso DESC";
                }
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
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $sql = "SELECT * FROM programaciones WHERE id = $id";
                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $data = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Programación no encontrada']);
                }
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            // Acción especial: importar programación desde otra materia
            if ($action === 'importar') {
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
                // Crear/Actualizar programación normal
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);

                if (isset($data['id']) && $data['id'] > 0) {
                    // Actualizar
                    $idMateria = intval($data['idMateria']);
                    $idGrupo = isset($data['idGrupo']) && $data['idGrupo'] ? intval($data['idGrupo']) : 'NULL';
                    $curso = mysqli_real_escape_string($db, $data['curso']);
                    $anyo = isset($data['anyo']) ? mysqli_real_escape_string($db, $data['anyo']) : '';
                    $profesor = isset($data['profesor']) ? mysqli_real_escape_string($db, $data['profesor']) : '';
                    $objetivos = isset($data['objetivos']) ? mysqli_real_escape_string($db, $data['objetivos']) : '';
                    $metodologia = isset($data['metodologia']) ? mysqli_real_escape_string($db, $data['metodologia']) : '';
                    $evaluacion = isset($data['evaluacion']) ? mysqli_real_escape_string($db, $data['evaluacion']) : '';
                    $atencion_diversidad = isset($data['atencion_diversidad']) ? mysqli_real_escape_string($db, $data['atencion_diversidad']) : '';
                    $materiales = isset($data['materiales']) ? mysqli_real_escape_string($db, $data['materiales']) : '';
                    $bibliografia = isset($data['bibliografia']) ? mysqli_real_escape_string($db, $data['bibliografia']) : '';
                    $id = intval($data['id']);
                    
                    $sql = "UPDATE programaciones SET
                            idMateria = $idMateria,
                            idGrupo = " . ($idGrupo === 'NULL' ? 'NULL' : $idGrupo) . ",
                            curso = '$curso',
                            anyo = '$anyo',
                            profesor = '$profesor',
                            objetivos = '$objetivos',
                            metodologia = '$metodologia',
                            evaluacion = '$evaluacion',
                            atencion_diversidad = '$atencion_diversidad',
                            materiales = '$materiales',
                            bibliografia = '$bibliografia'
                            WHERE id = $id";
                    $result = mysqli_query($db, $sql);
                    if (!$result) {
                        throw new Exception(mysqli_error($db));
                    }
                    $idRetorno = $id;
                } else {
                    // Crear
                    $idMateria = intval($data['idMateria']);
                    $idGrupo = isset($data['idGrupo']) && $data['idGrupo'] ? intval($data['idGrupo']) : 'NULL';
                    $curso = mysqli_real_escape_string($db, $data['curso']);
                    $anyo = isset($data['anyo']) ? mysqli_real_escape_string($db, $data['anyo']) : '';
                    $profesor = isset($data['profesor']) ? mysqli_real_escape_string($db, $data['profesor']) : '';
                    $objetivos = isset($data['objetivos']) ? mysqli_real_escape_string($db, $data['objetivos']) : '';
                    $metodologia = isset($data['metodologia']) ? mysqli_real_escape_string($db, $data['metodologia']) : '';
                    $evaluacion = isset($data['evaluacion']) ? mysqli_real_escape_string($db, $data['evaluacion']) : '';
                    $atencion_diversidad = isset($data['atencion_diversidad']) ? mysqli_real_escape_string($db, $data['atencion_diversidad']) : '';
                    $materiales = isset($data['materiales']) ? mysqli_real_escape_string($db, $data['materiales']) : '';
                    $bibliografia = isset($data['bibliografia']) ? mysqli_real_escape_string($db, $data['bibliografia']) : '';
                    
                    $sql = "INSERT INTO programaciones (idMateria, idGrupo, curso, anyo, profesor, objetivos, metodologia, evaluacion, atencion_diversidad, materiales, bibliografia)
                            VALUES ($idMateria, " . ($idGrupo === 'NULL' ? 'NULL' : $idGrupo) . ", '$curso', '$anyo', '$profesor', '$objetivos', '$metodologia', '$evaluacion', '$atencion_diversidad', '$materiales', '$bibliografia')";
                    $result = mysqli_query($db, $sql);
                    if (!$result) {
                        throw new Exception(mysqli_error($db));
                    }
                    $idRetorno = mysqli_insert_id($db);
                }

                echo json_encode(['success' => true, 'id' => $idRetorno]);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $sql = "DELETE FROM programaciones WHERE id = $id";
                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('ID no proporcionado');
            }
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
