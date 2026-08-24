<?php
// ============================================================================
// FASE 2.6 — Gestión de temas / unidades de programación
// Equivalente a v3: temas.php, editar_tema.php y ajax/temas/*
//   - listar_materias     : materias con programación activa (selector)
//   - listar              : temas de una materia (+ horas anuales para totales)
//   - obtener             : datos completos de un tema + CE/competencias (prefill)
//   - accordion_ra_ce     : acordeón RA/CE + checkboxes de competencias
//   - nuevo               : insertar un tema (modal: nº + título)
//   - guardar             : actualizar tema + reemplazar CE y competencias
//   - borrar              : eliminar tema y sus relaciones
//   - recalcular_porcentajes : recalcular % de evaluación de los RA
//   - repetir_evaluacion    : copiar el campo "evaluación" a toda la materia
//   - actualizar_ra        : editar porcentaje/es_clave de un RA concreto
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$session = checkSession();

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$body = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $body = $decoded;
    }
}
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === '' && isset($body['action'])) {
    $action = $body['action'];
}

$db = Db::open();

// ---------------------------------------------------------------------------
// Helpers (definidos una sola vez por petición)
// ---------------------------------------------------------------------------

// id del ciclo formativo al que pertenece la materia (0 si no es de FP)
// Copia de v3 obtenerIdCicloPorMateria().
function temas_id_ciclo_por_materia($db, $idMateria) {
    $fila = $db->fetchOne("SELECT ciclos.id
                FROM ciclos
                INNER JOIN cursos_ciclos ON ciclos.id = cursos_ciclos.idCiclo
                INNER JOIN cursos ON cursos_ciclos.idCurso = cursos.id
                INNER JOIN materias ON materias.idCurso = cursos.id
                WHERE materias.id = ? LIMIT 1", $idMateria);
    return $fila ? intval($fila['id']) : 0;
}

// Horas anuales de una materia (v3 obtenerHorasAnualesPorMateria)
function temas_horas_anuales($db, $idMateria) {
    $fila = $db->fetchOne("SELECT horas_anuales FROM materias WHERE id = ?", $idMateria);
    return $fila ? intval($fila['horas_anuales']) : 0;
}

// Checkbox de competencias de una materia (v3 generarCheckboxesCompetenciasMateria)
function temas_competencias_materia($db, $idMateria, $idCiclo) {
    $competencias = [];
    $yaAgregados = [];

    // Tipo 1: asignadas a la materia
    foreach ($db->fetchAll("SELECT cc.id, cc.codigo, cc.texto, cc.tipo, cc.orden
                FROM competencias_ciclos cc
                INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
                WHERE cm.idMateria = ? AND cc.tipo = 1
                ORDER BY cc.orden", $idMateria) as $fila) {
        $competencias[] = $fila;
        $yaAgregados[intval($fila['id'])] = true;
    }

    // Tipo 2: del ciclo (siempre)
    if ($idCiclo > 0) {
        foreach ($db->fetchAll("SELECT id, codigo, texto, tipo, orden
                    FROM competencias_ciclos
                    WHERE idCiclo = ? AND tipo = 2
                    ORDER BY orden", $idCiclo) as $fila) {
            $id = intval($fila['id']);
            if (!isset($yaAgregados[$id])) {
                $competencias[] = $fila;
                $yaAgregados[$id] = true;
            }
        }
    }

    // Ordenar por (tipo, orden)
    usort($competencias, function ($a, $b) {
        if (intval($a['tipo']) === intval($b['tipo'])) {
            return intval($a['orden']) - intval($b['orden']);
        }
        return intval($a['tipo']) - intval($b['tipo']);
    });

    return $competencias;
}

function temas_salida($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ---------------------------------------------------------------------------
// Dispatch
// ---------------------------------------------------------------------------
try {
    if ($method === 'GET') {

        // ------------------------------------------------------------------
        // Listar materias con programación activa (para el selector)
        // ------------------------------------------------------------------
        if ($action === 'listar_materias') {
            // Fiel a v3 (cargar_materias_programaciones.php): el profesor solo ve
            // las materias que imparte en los escenarios actuales; el jefe, las de
            // su departamento; el admin, todas (v4 no tiene el selector de v3).
            $rol = isset($session['rol']) ? $session['rol'] : '';
            if ($rol === ROLE_PROFESOR) {
                $idProfesor = (int)$session['idUsuario'];
                $sql = "SELECT DISTINCT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales
                          FROM materias m
                          LEFT JOIN cursos c ON c.id = m.idCurso
                          LEFT JOIN seleccion s ON s.idMateria = m.id
                          LEFT JOIN escenarios_desideratas e ON e.id = s.idEscenario
                          WHERE m.tiene_programacion = 1 AND e.actual = 1 AND s.idProfesor = $idProfesor
                          ORDER BY m.nombre";
            } else {
                $idDepartamento = !empty($session['idDepartamento']) ? (int)$session['idDepartamento'] : 0;
                $sql = "SELECT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales
                          FROM materias m
                          LEFT JOIN cursos c ON c.id = m.idCurso
                          WHERE m.tiene_programacion = 1";
                if ($idDepartamento > 0) {
                    $sql .= " AND m.idDepartamento = $idDepartamento";
                }
                $sql .= " ORDER BY c.orden, c.nombre, m.nombre";
            }
            $materias = [];
            foreach ($db->fetchAll($sql) as $fila) {
                $idMateria = intval($fila['id']);
                $materias[] = [
                    'id' => $idMateria,
                    'materia' => $fila['materia'],
                    'curso' => $fila['curso'],
                    'horas_anuales' => intval($fila['horas_anuales']),
                    'idCiclo' => temas_id_ciclo_por_materia($db, $idMateria)
                ];
            }
            temas_salida(['success' => true, 'data' => $materias]);

        // --------------------------------------------------------------------
        // Listar temas de una materia (como v3 mostrarTemasPorMateria)
        // ------------------------------------------------------------------
        } elseif ($action === 'listar') {
            $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }
            $temas = [];
            foreach ($db->fetchAll("SELECT id, orden, titulo, horas, peso_evaluacion
                        FROM temas WHERE idMateria = ? ORDER BY orden", $idMateria) as $fila) {
                $temas[] = [
                    'id' => intval($fila['id']),
                    'orden' => intval($fila['orden']),
                    'titulo' => $fila['titulo'],
                    'horas' => intval($fila['horas']),
                    'peso_evaluacion' => intval($fila['peso_evaluacion'])
                ];
            }
            temas_salida(['success' => true, 'data' => [
                'temas' => $temas,
                'horas_anuales' => temas_horas_anuales($db, $idMateria)
            ]]);

        // --------------------------------------------------------------------
        // Datos de un tema (prefill del formulario) + CE/competencias
        // ------------------------------------------------------------------
        } elseif ($action === 'obtener') {
            $idTema = isset($_GET['idTema']) ? intval($_GET['idTema']) : 0;
            if ($idTema <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar un tema'], 400);
            }
            $fila = $db->fetchOne("SELECT * FROM temas WHERE id = ?", $idTema);
            if (!$fila) {
                temas_salida(['success' => false, 'error' => 'Tema no encontrado'], 404);
            }

            $criterios = [];
            foreach ($db->fetchAll("SELECT idRA, codigo FROM criterios_temas WHERE idTema = ?", $idTema) as $c) {
                $criterios[] = ['idRA' => intval($c['idRA']), 'codigo' => $c['codigo']];
            }

            $competencias = [];
            foreach ($db->fetchAll("SELECT idCompetencia FROM competencias_temas WHERE idTema = ?", $idTema) as $c) {
                $competencias[] = intval($c['idCompetencia']);
            }

            $idMateria = intval($fila['idMateria']);
            temas_salida(['success' => true, 'data' => [
                'tema' => [
                    'id' => intval($fila['id']),
                    'idMateria' => $idMateria,
                    'orden' => intval($fila['orden']),
                    'titulo' => $fila['titulo'],
                    'horas' => intval($fila['horas']),
                    'trimestre' => intval($fila['trimestre']),
                    'peso_evaluacion' => intval($fila['peso_evaluacion']),
                    'descripcion' => $fila['descripcion'],
                    'justificacion' => $fila['justificacion'],
                    'contexto' => $fila['contexto'],
                    'contenidos' => $fila['contenidos'],
                    'secuenciacion' => $fila['secuenciacion'],
                    'recursos' => $fila['recursos'],
                    'evaluacion' => $fila['evaluacion'],
                    'metodologia' => $fila['metodologia'],
                    'adaptaciones' => $fila['adaptaciones'],
                    'contexto_defecto' => intval($fila['contexto_defecto']),
                    'recursos_defecto' => intval($fila['recursos_defecto']),
                    'metodologia_defecto' => intval($fila['metodologia_defecto']),
                    'adaptaciones_defecto' => intval($fila['adaptaciones_defecto'])
                ],
                'criterios' => $criterios,
                'competencias' => $competencias
            ]]);

        // --------------------------------------------------------------------
        // Acordeón RA/CE + competencias (nivel materia)
        // ------------------------------------------------------------------
        } elseif ($action === 'accordion_ra_ce') {
            $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }
            $idCiclo = temas_id_ciclo_por_materia($db, $idMateria);

            $ra = [];
            $total = 0;
            foreach ($db->fetchAll("SELECT id, orden, texto, porcentaje_evaluacion, es_clave
                        FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", $idMateria) as $fila) {
                $idRA = intval($fila['id']);
                $total += intval($fila['porcentaje_evaluacion']);

                $ce = [];
                foreach ($db->fetchAll("SELECT codigo, texto FROM criterios_evaluacion WHERE idRA = ? ORDER BY codigo", $idRA) as $c) {
                    $ce[] = ['idRA' => $idRA, 'codigo' => $c['codigo'], 'texto' => $c['texto']];
                }

                $ra[] = [
                    'id' => $idRA,
                    'orden' => intval($fila['orden']),
                    'texto' => $fila['texto'],
                    'porcentaje_evaluacion' => intval($fila['porcentaje_evaluacion']),
                    'es_clave' => intval($fila['es_clave']),
                    'ce' => $ce
                ];
            }

            $competencias = temas_competencias_materia($db, $idMateria, $idCiclo);

            temas_salida(['success' => true, 'data' => [
                'idCiclo' => $idCiclo,
                'ra' => $ra,
                'total' => $total,
                'competencias' => $competencias
            ]]);

        } else {
            temas_salida(['success' => false, 'error' => 'Acción no válida'], 400);
        }

    } elseif ($method === 'POST') {

        // --------------------------------------------------------------------
        // Insertar un nuevo tema (solo nº + título; resto por defecto)
        // ------------------------------------------------------------------
        if ($action === 'nuevo') {
            $idMateria = isset($body['idMateria']) ? intval($body['idMateria']) : 0;
            $orden     = isset($body['orden']) ? intval($body['orden']) : 0;
            $titulo    = isset($body['titulo']) ? trim($body['titulo']) : '';

            if ($idMateria <= 0 || $orden <= 0 || $titulo === '') {
                temas_salida(['success' => false, 'error' => 'Indica el número y el título del tema'], 400);
            }

            // Nota: titulo se inserta como '' aquí; se actualiza a continuación para respetar el texto.
            $db->execute("INSERT INTO temas
                        (idMateria, orden, titulo, horas, trimestre, peso_evaluacion,
                         descripcion, justificacion, contexto, contenidos, secuenciacion,
                         recursos, evaluacion, metodologia, adaptaciones,
                         contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                        VALUES (?, ?, '', 0, 0, 0, '', '', '', '', '', '', '', '', '', 1, 1, 1, 1)",
                        $idMateria, $orden);
            $nuevoId = $db->insertId();
            $db->execute("UPDATE temas SET titulo = ? WHERE id = ?", $titulo, $nuevoId);

            temas_salida(['success' => true, 'message' => 'Tema creado correctamente', 'data' => ['id' => $nuevoId]]);

        // ----------------------------------------------------------------------
        // Actualizar tema + reemplazar CE y competencias
        // ----------------------------------------------------------------------
        } elseif ($action === 'guardar') {
            $idTema = isset($body['idTema']) ? intval($body['idTema']) : 0;
            if ($idTema <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar el tema a guardar'], 400);
            }

            $orden            = isset($body['orden']) ? intval($body['orden']) : 0;
            $titulo           = isset($body['titulo']) ? trim($body['titulo']) : '';
            $horas            = isset($body['horas']) ? intval($body['horas']) : 0;
            $trimestre        = isset($body['trimestre']) ? intval($body['trimestre']) : 0;
            $peso             = isset($body['peso_evaluacion']) ? intval($body['peso_evaluacion']) : 0;
            $descripcion      = isset($body['descripcion']) ? $body['descripcion'] : '';
            $justificacion    = isset($body['justificacion']) ? $body['justificacion'] : '';
            $contexto         = isset($body['contexto']) ? $body['contexto'] : '';
            $contenidos       = isset($body['contenidos']) ? $body['contenidos'] : '';
            $secuenciacion    = isset($body['secuenciacion']) ? $body['secuenciacion'] : '';
            $recursos         = isset($body['recursos']) ? $body['recursos'] : '';
            $evaluacion       = isset($body['evaluacion']) ? $body['evaluacion'] : '';
            $metodologia      = isset($body['metodologia']) ? $body['metodologia'] : '';
            $adaptaciones     = isset($body['adaptaciones']) ? $body['adaptaciones'] : '';
            $contextoDefecto  = !empty($body['contexto_defecto']) ? 1 : 0;
            $recursosDefecto  = !empty($body['recursos_defecto']) ? 1 : 0;
            $metodologiaDefecto = !empty($body['metodologia_defecto']) ? 1 : 0;
            $adaptacionesDefecto = !empty($body['adaptaciones_defecto']) ? 1 : 0;

            try {
                $db->begin();
                $afectados = $db->execute("UPDATE temas SET
                        orden = ?, titulo = ?, horas = ?, trimestre = ?, peso_evaluacion = ?,
                        descripcion = ?, justificacion = ?, contexto = ?, contenidos = ?,
                        secuenciacion = ?, recursos = ?, evaluacion = ?, metodologia = ?, adaptaciones = ?,
                        contexto_defecto = ?, recursos_defecto = ?, metodologia_defecto = ?, adaptaciones_defecto = ?
                        WHERE id = ?",
                        $orden, $titulo, $horas, $trimestre, $peso,
                        $descripcion, $justificacion, $contexto, $contenidos,
                        $secuenciacion, $recursos, $evaluacion, $metodologia, $adaptaciones,
                        $contextoDefecto, $recursosDefecto, $metodologiaDefecto, $adaptacionesDefecto,
                        $idTema);

                // Reemplazar criterios de evaluación (CE)
                $db->execute("DELETE FROM criterios_temas WHERE idTema = ?", $idTema);
                $criterios = isset($body['criterios']) && is_array($body['criterios']) ? $body['criterios'] : [];
                foreach ($criterios as $ce) {
                    if (!is_array($ce) || !isset($ce['idRA'], $ce['codigo'])) {
                        continue;
                    }
                    $db->execute("INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)",
                        intval($ce['idRA']), $ce['codigo'], $idTema);
                }

                // Reemplazar competencias
                $db->execute("DELETE FROM competencias_temas WHERE idTema = ?", $idTema);
                $competencias = isset($body['competencias']) && is_array($body['competencias']) ? $body['competencias'] : [];
                foreach ($competencias as $com) {
                    $db->execute("INSERT INTO competencias_temas (idCompetencia, idTema) VALUES (?, ?)",
                        intval($com), $idTema);
                }

                $db->commit();
            } catch (DbException $e) {
                $db->rollback();
                throw $e;
            }

            temas_salida(['success' => true,
                'errorTema' => ($afectados == 0),
                'errorCriterios' => false,
                'errorCompetencias' => false,
                'message' => 'Tema guardado correctamente']);

        // ----------------------------------------------------------------------
        // Borrar tema + relaciones
        // ----------------------------------------------------------------------
        } elseif ($action === 'borrar') {
            $idTema = isset($body['id']) ? intval($body['id']) : 0;
            if ($idTema <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar el tema a borrar'], 400);
            }

            try {
                $db->begin();
                foreach (['competencias_temas', 'criterios_temas', 'programaciones_aula_temas'] as $tabla) {
                    $db->execute("DELETE FROM {$tabla} WHERE idTema = ?", $idTema);
                }
                $db->execute("DELETE FROM temas WHERE id = ?", $idTema);
                $db->commit();
            } catch (DbException $e) {
                $db->rollback();
                throw $e;
            }

            temas_salida(['success' => true, 'message' => 'Tema eliminado correctamente']);

        // ----------------------------------------------------------------------
        // Recalcular porcentajes de evaluación de los RA (v3 calcularPorcentajesRA)
        // ----------------------------------------------------------------------
        } elseif ($action === 'recalcular_porcentajes') {
            $idMateria = isset($body['idMateria']) ? intval($body['idMateria']) : 0;
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }

            $listadoRA = [];
            foreach ($db->fetchAll("SELECT ra.id, ra.orden, COUNT(ct.codigo) AS num_criterios
                        FROM resultados_aprendizaje ra
                        LEFT JOIN criterios_temas ct ON ra.id = ct.idRA
                        WHERE ra.idMateria = ?
                        GROUP BY ra.id, ra.orden
                        ORDER BY ra.orden", $idMateria) as $fila) {
                $listadoRA[] = ['id' => intval($fila['id']), 'num_criterios' => intval($fila['num_criterios'])];
            }

            if (!empty($listadoRA)) {
                $filaTotal = $db->fetchOne("SELECT COUNT(*) AS total
                            FROM resultados_aprendizaje ra
                            INNER JOIN criterios_temas ct ON ra.id = ct.idRA
                            WHERE ra.idMateria = ?", $idMateria);
                $totalCriterios = $filaTotal ? intval($filaTotal['total']) : 0;
            } else {
                $totalCriterios = 0;
            }

            $porcentajes = [];
            $suma = 0;
            foreach ($listadoRA as $item) {
                $num = $item['num_criterios'];
                $porcentaje = $totalCriterios > 0 ? intval(($num / $totalCriterios) * 100) : 0;
                $porcentajes[] = ['id' => $item['id'], 'porcentaje' => $porcentaje];
                $suma += $porcentaje;
            }

            // Si la suma no llega a 100, repartir el resto en los últimos con valor > 0
            if ($suma > 0 && $suma < 100) {
                for ($i = count($porcentajes) - 1; $i >= 0 && $suma < 100; $i--) {
                    if ($porcentajes[$i]['porcentaje'] > 0) {
                        $porcentajes[$i]['porcentaje']++;
                        $suma++;
                    }
                }
            }

            foreach ($porcentajes as $p) {
                $db->execute("UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ? WHERE id = ?",
                    $p['porcentaje'], $p['id']);
            }

            temas_salida(['success' => true, 'message' => 'Porcentajes recalculados',
                'data' => ['ra' => $porcentajes]]);

        // ----------------------------------------------------------------------
        // Copiar el campo "evaluación" a todos los temas de la materia
        // ----------------------------------------------------------------------
        } elseif ($action === 'repetir_evaluacion') {
            $idMateria  = isset($body['idMateria']) ? intval($body['idMateria']) : 0;
            $evaluacion = isset($body['evaluacion']) ? $body['evaluacion'] : '';
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }

            $afectados = $db->execute("UPDATE temas SET evaluacion = ? WHERE idMateria = ?",
                $evaluacion, $idMateria);

            temas_salida(['success' => true,
                'message' => 'Campo de evaluación copiado en todos los temas de la materia',
                'data' => ['actualizados' => $afectados]]);

        // ----------------------------------------------------------------------
        // Editar porcentaje/es_clave de un RA concreto
        // ----------------------------------------------------------------------
        } elseif ($action === 'actualizar_ra') {
            $idRA    = isset($body['idRA']) ? intval($body['idRA']) : 0;
            $porcentaje = isset($body['porcentaje_evaluacion']) ? intval($body['porcentaje_evaluacion']) : 0;
            $esClave   = !empty($body['es_clave']) ? 1 : 0;

            if ($idRA <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar un resultado de aprendizaje'], 400);
            }

            $db->execute("UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ?, es_clave = ? WHERE id = ?",
                $porcentaje, $esClave, $idRA);

            temas_salida(['success' => true, 'message' => 'Resultado de aprendizaje actualizado']);

        } else {
            temas_salida(['success' => false, 'error' => 'Acción no válida'], 400);
        }

    } else {
        temas_salida(['success' => false, 'error' => 'Método no permitido'], 405);
    }

} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
