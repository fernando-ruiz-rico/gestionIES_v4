<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = Db::open();

// ---------------------------------------------------------------------------
// FASE 2.1 — Edición de apartados (fiel a v3/programaciones.php + ajax/programaciones).
//
// La programación de una materia vive en `apartados_programaciones` (la
// definición) + `contenidos_programaciones` (el texto por materia). Un apartado
// con `tipo = 0` es EDITABLE (se rellena a mano, TinyMCE en v3); el resto se
// rellena automáticamente a partir de otros apartados (Unidades, RA/CE, FE...)
// y solo se editan en sus propias secciones.
// ---------------------------------------------------------------------------

// ¿La materia pertenece a un ciclo? → 'FP'; si no, 'ESO/BACH' (criterio v3).
function programacionesCategoria($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $fila = $db->fetchOne(
        "SELECT c.id FROM ciclos c
            JOIN cursos_ciclos cc ON cc.idCiclo = c.id
            JOIN cursos cu ON cu.id = cc.idCurso
            JOIN materias m ON m.idCurso = cu.id
           WHERE m.id = $idMateria
           LIMIT 1");
    return $fila ? 'FP' : 'ESO/BACH';
}

// Listado de apartados de una materia (con numeración "1." / "1.1."), criterio v3.
function programacionesCargarApartados($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $categoria = programacionesCategoria($db, $idMateria);
    try {
        $filas = $db->fetchAll(
            "SELECT id, titulo, tipo, subapartado FROM apartados_programaciones
              WHERE categoria = 'TODOS' OR categoria = ?
              ORDER BY orden", $categoria);
    } catch (DbException $e) {
        throw new Exception('Error consultando apartados: ' . $e->getMessage());
    }
    $apartados = [];
    $principal = 0;
    $secundario = 0;
    foreach ($filas as $fila) {
        if (!(bool)$fila['subapartado']) {
            $principal++;
            $secundario = 0;
        } else {
            $secundario++;
        }
        $apartados[] = [
            'id'        => (int)$fila['id'],
            'tipo'      => (int)$fila['tipo'],
            'nombre'    => ($fila['subapartado']
                                 ? "$principal.$secundario. "
                                 : "$principal. ") . $fila['titulo']
        ];
    }
    return $apartados;
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

                $programaciones = $db->fetchAll($sql);
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

                // Un apartado puede tener varios contenidos; se agrupan por orden de id.
                $apartados = [];
                $posicion = [];
                foreach ($db->fetchAll($sql) as $fila) {
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

                if (empty($apartados)) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'La materia no tiene programación']);
                } else {
                    echo json_encode(['success' => true, 'data' => $apartados]);
                }
            } elseif ($action === 'cargar_materias') {
                // FASE 2.1 — Desplegable de materias (fiel a v3/cargar_materias_programaciones.php).
                // Por rol: el profesor solo ve las suyas; el jefe, las de su departamento;
                // el admin, todas (v4 no tiene la selección de departamento de v3).
                $session = checkSession();
                $rol = $session['rol'];

                if ($rol === ROLE_PROFESOR) {
                    $idProfesor = (int)$session['idUsuario'];
                    $sql = "SELECT DISTINCT m.id AS id, m.nombre AS nomMateria, cu.nombre AS nomCurso
                              FROM materias m
                              JOIN cursos cu ON cu.id = m.idCurso
                              JOIN seleccion s ON s.idMateria = m.id
                              JOIN escenarios_desideratas e ON e.id = s.idEscenario
                             WHERE s.idProfesor = $idProfesor
                               AND m.tiene_programacion = 1
                               AND e.actual = 1
                             ORDER BY m.nombre";
                    $materias = [];
                    foreach ($db->fetchAll($sql) as $fila) {
                        $materias[] = [
                            'id'       => (int)$fila['id'],
                            'nombre'   => $fila['nomMateria'] . ' (' . $fila['nomCurso'] . ')'
                        ];
                    }
                } elseif (esUsuarioSuper($rol)) {
                    // Fiel a v3: el jefe ve las del departamento; el admin (sin
                    // selector de departamento en v4) ve todas.
                    $idDepartamento = !empty($session['idDepartamento']) ? (int)$session['idDepartamento'] : 0;
                    $sql = "SELECT DISTINCT m.id AS id, m.nombre AS nombre, c.orden AS ordenCurso
                              FROM materias m
                              JOIN cursos c ON c.id = m.idCurso
                             WHERE m.tiene_programacion = 1";
                    if ($idDepartamento > 0) {
                        $sql .= " AND m.idDepartamento = $idDepartamento";
                    }
                    $sql .= " ORDER BY c.orden, m.nombre";
                    $materias = [];
                    foreach ($db->fetchAll($sql) as $fila) {
                        $materias[] = ['id' => (int)$fila['id'], 'nombre' => $fila['nombre']];
                    }
                } else {
                    throw new Exception('Rol no reconocido');
                }

                echo json_encode(['success' => true, 'data' => $materias]);
            } elseif ($action === 'cargar_apartados') {
                // FASE 2.1 — Apartados de una materia (fiel a v3/cargar_apartados.php).
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
                if ($idMateria <= 0) {
                    throw new Exception('ID de materia inválido');
                }
                echo json_encode(['success' => true, 'data' => programacionesCargarApartados($db, $idMateria)]);
            } elseif ($action === 'cargar_contenido') {
                // FASE 2.1 — Texto de un apartado de una materia (v3/cargar_contenido_programacion).
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
                $idApartado = isset($_GET['idApartado']) ? intval($_GET['idApartado']) : 0;
                if ($idMateria <= 0 || $idApartado <= 0) {
                    throw new Exception('ID de materia o apartado inválido');
                }
                $fila = $db->fetchOne(
                    "SELECT texto FROM contenidos_programaciones WHERE idMateria = $idMateria AND idApartado = $idApartado");
                echo json_encode(['success' => true, 'data' => ['texto' => $fila ? $fila['texto'] : '']]);
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

                try {
                    $db->begin();

                    // Borrar contenidos previos de programación destino
                    $db->execute("DELETE FROM contenidos_programaciones WHERE idMateria = $idMateriaDestino");
                    $db->execute("DELETE FROM competencias_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = $idMateriaDestino)");
                    $db->execute("DELETE FROM criterios_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = $idMateriaDestino)");
                    $db->execute("DELETE FROM temas WHERE idMateria = $idMateriaDestino");

                    // Insertar contenidos de la materia origen en la destino
                    $db->execute("INSERT INTO contenidos_programaciones(idMateria, idApartado, texto)
                                   SELECT $idMateriaDestino AS idMateria, idApartado, texto
                                   FROM contenidos_programaciones WHERE idMateria = $idMateriaOrigen");

                    // Insertar temas
                    $db->execute("INSERT INTO temas(idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                                   SELECT $idMateriaDestino AS idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto
                                   FROM temas WHERE idMateria = $idMateriaOrigen");

                    // Insertar RA y CE asociados
                    foreach ($db->fetchAll("SELECT criterios_temas.codigo as CE, temas.orden as tema, resultados_aprendizaje.orden as RA
                                            FROM criterios_temas, temas, resultados_aprendizaje
                                            WHERE criterios_temas.idRA = resultados_aprendizaje.id
                                              AND criterios_temas.idTema = temas.id
                                              AND temas.idMateria = $idMateriaOrigen") as $fila) {
                        $codigoCE = $fila['CE'];
                        $ordenRA = intval($fila['RA']);
                        $numTema = intval($fila['tema']);

                        // Buscar el id del RA para la materia destino
                        $row2 = $db->fetchOne("SELECT id FROM resultados_aprendizaje WHERE idMateria = $idMateriaDestino AND orden = $ordenRA");
                        $idRA = $row2 ? $row2['id'] : null;

                        // Buscar el id del tema para la materia destino
                        $row2 = $db->fetchOne("SELECT id FROM temas WHERE idMateria = $idMateriaDestino AND orden = $numTema");
                        $idTema = $row2 ? $row2['id'] : null;

                        if ($idRA && $idTema) {
                            $db->execute("INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)",
                                $idRA, $codigoCE, $idTema);
                        }
                    }

                    $db->commit();
                } catch (DbException $e) {
                    $db->rollback();
                    throw $e;
                }

                echo json_encode(['success' => true, 'message' => 'Programación importada correctamente']);
            } elseif ($action === 'guardar_contenido') {
                // FASE 2.1 — Guardar el texto de un apartado editable (v3/insertar_contenido_programacion).
                // Permiso: sesión válida (v3 lo permite a cualquier usuario con sesión, y la
                // visibilidad del editor ya la controla la vista según rol/activación).
                checkSession();
                $input = json_decode(file_get_contents('php://input'), true);
                if (!is_array($input)) {
                    throw new Exception('Datos de entrada no válidos');
                }
                $idMateria = isset($input['idMateria']) ? intval($input['idMateria']) : 0;
                $idApartado = isset($input['idApartado']) ? intval($input['idApartado']) : 0;
                if ($idMateria <= 0 || $idApartado <= 0) {
                    throw new Exception('ID de materia o apartado inválido');
                }
                $texto = isset($input['texto']) ? $input['texto'] : '';

                $fila = $db->fetchOne(
                    "SELECT id FROM contenidos_programaciones WHERE idMateria = $idMateria AND idApartado = $idApartado");

                // Fiel a v3/insertar_contenido_programacion.php: si el texto es
                // idéntico al ya guardado, MySQL no modifica filas → "sin cambios".
                $existia = (bool)$fila;
                if ($existia) {
                    $modificadas = $db->execute(
                        "UPDATE contenidos_programaciones SET texto = ? WHERE idMateria = $idMateria AND idApartado = $idApartado",
                        $texto);
                } else {
                    $modificadas = $db->execute(
                        "INSERT INTO contenidos_programaciones (idMateria, idApartado, texto) VALUES (?, ?, ?)",
                        $idMateria, $idApartado, $texto);
                }

                $sinCambios = !$existia ? false : ($modificadas == 0);
                echo json_encode([
                    'success'      => true,
                    'sin_cambios' => (bool)$sinCambios,
                    'message'     => $sinCambios ? 'El contenido ya estaba guardado así' : 'Contenido guardado correctamente'
                ]);
            } else {
                // FASE 2.1 — No hay fila única de programación que eliminar a este nivel.
                throw new Exception('Las programaciones se gestionan por apartados y contenidos; aquí solo se puede listar, ver, importar y guardar el contenido de un apartado.');
            }
            break;

        case 'DELETE':
            // FASE 2.1 — No hay fila única que eliminar aquí (en v3 se borrarían sus apartados/contenidos, fase 2.5).
            throw new Exception('Eliminar una programación se gestiona con el borrado de sus apartados y contenidos (fase 2.5).');
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
