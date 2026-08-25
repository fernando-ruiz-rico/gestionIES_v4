<?php
// API de "Estadísticas" del módulo de Desideratas, fiel a v3/estadisticas.php:
//  - horas por especialidad: horas impartidas por la especialidad y las
//    estipuladas (nº de profesores de la especialidad * horas por profesor)
//  - materias sin escoger: a las que nadie eligió, o que "fallan" por pocas peticiones
//    o por quedarse sin profesores
//  - conflictos: sobredemanda, falta de divisibilidad, pocas peticiones, mínimos de
//    profesores y máximos de grupos por profesor, y profesores con más de una tutoría
require_once '../config.php';
cabeceraJson();

// Fiel a v3: el módulo de Desideratas exige sesión iniciada
$usuario = checkSession();

// Horas lectivas de referencia para un profesor (según v3)
define('HORAS_PROFESOR', 18);

$action = getOptimo('action');

try {
    $db = Db::open();
    switch ($action) {
        case 'listar': {
            $idEscenario = getOptimoInt('idEscenario');
            $idDepartamento = getOptimoInt('idDepartamento');
            if ($idEscenario <= 0 || $idDepartamento <= 0) {
                throw new Exception('Faltan parámetros');
            }

            // ---- Horas por especialidad ----
            $especialidades = $db->fetchAll("SELECT id, descripcion, profesores
                                             FROM especialidades
                                             WHERE idDepartamento = ?
                                             ORDER BY id", $idDepartamento);
            $horasPorEspecialidad = array();
            foreach ($especialidades as $esp) {
                // Horas impartidas por los profesores de esta especialidad
                $suma = $db->fetchOne("SELECT COALESCE(SUM(s.horas), 0) AS total
                                       FROM seleccion s
                                       JOIN profesores p ON p.id = s.idProfesor
                                       WHERE p.idEspecialidad = ? AND p.idDepartamento = ? AND s.idEscenario = ?",
                                      $esp['id'], $idDepartamento, $idEscenario);
                $horasPorEspecialidad[] = array(
                    'id' => $esp['id'],
                    'descripcion' => $esp['descripcion'],
                    'horasTotales' => $suma['total'],
                    // v3: la referencia es el nº de profesores de la especialidad * HORAS_PROFESOR
                    'horasRef' => $esp['profesores'] * HORAS_PROFESOR
                );
            }

            // ---- Materias sin escoger y conflictos ----
            // (v3 recorre cursos y grupos; solo le importan las materias del
            //  departamento con cantidad > 0, que son las que se consultan aquí)
            $materias = $db->fetchAll("SELECT m.id, m.nombre, m.idEspecialidad, m.divisible,
                                            c.nombre AS nombreCurso, g.id AS idGrupo, g.nombre AS nombreGrupo,
                                            mg.cantidad, mg.horas, mg.min_num_profesores, mg.max_grupos_profesor
                                       FROM materias m
                                       JOIN materias_grupos mg ON mg.idMateria = m.id
                                       JOIN cursos c ON c.id = m.idCurso
                                       JOIN grupos g ON g.id = mg.idGrupo
                                       WHERE m.idDepartamento = ? AND mg.cantidad > 0
                                       ORDER BY c.orden, g.orden, g.nombre, m.nombre", $idDepartamento);

            // El profesor logueado se marca en los conflictos en los que participa.
            // El admin (idUsuario 'admin') no es un profesor, así que nunca le toca
            $idUsuario = ($usuario['idUsuario'] === 'admin') ? null : $usuario['idUsuario'];
            $noEscogidas = array();
            $conflictos = array();

            foreach ($materias as $materia) {
                $datos = $materia['nombre'] . " (" . $materia['nombreCurso'] . " " . $materia['nombreGrupo'] . ")";
                // Quién la eligió y cuántas horas en total
                $peticiones = $db->fetchAll("SELECT s.idProfesor, p.nombre AS nombreProfesor, s.horas
                                             FROM seleccion s
                                             JOIN profesores p ON p.id = s.idProfesor
                                             WHERE s.idMateria = ? AND s.idGrupo = ? AND s.idEscenario = ?
                                             ORDER BY s.orden", $materia['id'], $materia['idGrupo'], $idEscenario);
                if (count($peticiones) == 0) {
                    // Nadie la ha elegido
                    $noEscogidas[] = array(
                        'especialidad' => $materia['idEspecialidad'] ? $materia['idEspecialidad'] : '',
                        'nombre' => $materia['nombre'],
                        'curso' => $materia['nombreCurso'],
                        'grupo' => $materia['nombreGrupo'],
                        'horas' => $materia['horas']
                    );
                } else {
                    $tuyo = false;
                    $sumHoras = 0;
                    foreach ($peticiones as $p) {
                        $sumHoras += $p['horas'];
                        if ($idUsuario !== null && $p['idProfesor'] == $idUsuario) {
                            $tuyo = true;
                        }
                    }
                    // Cadena de comprobaciones de v3, en su orden: la primera que
                    // se cumple es la que da el mensaje
                    if (!$materia['divisible'] && count($peticiones) > $materia['cantidad']) {
                        $conflictos[] = array('texto' => $datos . " no es divisible y tiene más peticiones de las permitidas", 'tuyo' => $tuyo);
                    } else if ($sumHoras > $materia['horas'] * $materia['cantidad']) {
                        $nombres = array();
                        foreach ($peticiones as $p) {
                            $nombres[] = $p['nombreProfesor'];
                        }
                        $conflictos[] = array('texto' => $datos . " tiene demasiadas peticiones (" . count($peticiones) . "): " . implode(', ', $nombres), 'tuyo' => $tuyo);
                    } else if ($sumHoras < $materia['horas'] * $materia['cantidad']) {
                        $noEscogidas[] = array(
                            'especialidad' => $materia['idEspecialidad'] ? $materia['idEspecialidad'] : '',
                            'nombre' => $materia['nombre'],
                            'curso' => $materia['nombreCurso'],
                            'grupo' => $materia['nombreGrupo'],
                            'horas' => $materia['horas']
                        );
                        $conflictos[] = array('texto' => $datos . " tiene pocas peticiones (" . count($peticiones) . ")", 'tuyo' => $tuyo);
                    } else if ($materia['min_num_profesores'] > 0 && contarProfesoresDiferentes($peticiones) < $materia['min_num_profesores']) {
                        $noEscogidas[] = array(
                            'especialidad' => $materia['idEspecialidad'] ? $materia['idEspecialidad'] : '',
                            'nombre' => $materia['nombre'],
                            'curso' => $materia['nombreCurso'],
                            'grupo' => $materia['nombreGrupo'],
                            'horas' => $materia['horas']
                        );
                        $conflictos[] = array('texto' => $datos . " requiere más profesores para impartirse", 'tuyo' => $tuyo);
                    } else if ($materia['max_grupos_profesor'] > 0) {
                        // ¿Alguien la eligió más veces de las que puede?
                        $idsUnicos = array_unique(array_column_ids($peticiones, 'idProfesor'));
                        foreach ($idsUnicos as $id) {
                            $veces = contarVeces($id, $peticiones);
                            if ($veces > $materia['max_grupos_profesor']) {
                                $conflictos[] = array('texto' => $datos . " ha sido elegida demasiadas veces por " . obtenerNombreProfesor($id, $peticiones), 'tuyo' => ($id == $idUsuario));
                            }
                        }
                    }
                }
            }

            // v3: profesores con más de una tutoría en el escenario
            $tutorias = $db->fetchAll("SELECT p.id, p.nombre
                                       FROM seleccion s
                                       JOIN materias m ON m.id = s.idMateria
                                       JOIN profesores p ON p.id = s.idProfesor
                                       WHERE m.tipo = 'TUTORIA' AND s.idEscenario = ?
                                       GROUP BY p.id, p.nombre
                                       HAVING COUNT(DISTINCT s.idMateria, s.idGrupo) >= 2
                                       ORDER BY p.orden", $idEscenario);
            foreach ($tutorias as $t) {
                $conflictos[] = array('texto' => $t['nombre'] . " ha escogido más de una tutoría.", 'tuyo' => ($idUsuario !== null && $t['id'] == $idUsuario));
            }

            // v3: el aviso de "Tienes conflictos" solo se muestra a quien no sea admin
            $tienesConflictos = false;
            foreach ($conflictos as $c) {
                if ($c['tuyo']) {
                    $tienesConflictos = true;
                }
            }

            // v3 ordena las materias sin escoger por especialidad (asc) y luego por curso y grupo
            usort($noEscogidas, function ($a, $b) {
                $cmp = strcmp((string)$a['especialidad'], (string)$b['especialidad']);
                if ($cmp === 0) {
                    return strcmp($a['curso'] . ' ' . $a['grupo'], $b['curso'] . ' ' . $b['grupo']);
                }
                return $cmp;
            });

            $db->close();
            sendJSONSuccess(array(
                'horasPorEspecialidad' => $horasPorEspecialidad,
                'noEscogidas' => $noEscogidas,
                'conflictos' => $conflictos,
                'tienesConflictos' => $tienesConflictos
            ));
            break;
        }
        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}

// Número de profesores distintos que aparecen en un listado de peticiones
function contarProfesoresDiferentes($peticiones) {
    $ids = array();
    foreach ($peticiones as $p) {
        $ids[] = $p['idProfesor'];
    }
    return count(array_unique($ids));
}

// Ids distintos de un listado de peticiones
function array_column_ids($peticiones, $campo) {
    $ids = array();
    foreach ($peticiones as $p) {
        $ids[] = $p[$campo];
    }
    return $ids;
}

// Cuántas veces aparece un profesor en un listado de peticiones
function contarVeces($idProfesor, $peticiones) {
    $veces = 0;
    foreach ($peticiones as $p) {
        if ($p['idProfesor'] == $idProfesor) {
            $veces++;
        }
    }
    return $veces;
}

// Nombre de un profesor a partir del listado de peticiones
function obtenerNombreProfesor($idProfesor, $peticiones) {
    foreach ($peticiones as $p) {
        if ($p['idProfesor'] == $idProfesor) {
            return $p['nombreProfesor'];
        }
    }
    return '';
}
?>
