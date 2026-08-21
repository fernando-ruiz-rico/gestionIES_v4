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
// Compatible con PHP 5 (mysqli_*, sentencias preparadas).
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

@session_start();
$session = $_SESSION;
if (empty($session['idUsuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
    exit;
}

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

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// ---------------------------------------------------------------------------
// Helpers (definidos una sola vez por petición)
// ---------------------------------------------------------------------------

// id del ciclo formativo al que pertenece la materia (0 si no es de FP)
// Copia de v3 obtenerIdCicloPorMateria().
function temas_id_ciclo_por_materia($db, $idMateria) {
    $stmt = mysqli_prepare($db, "SELECT ciclos.id
                FROM ciclos
                INNER JOIN cursos_ciclos ON ciclos.id = cursos_ciclos.idCiclo
                INNER JOIN cursos ON cursos_ciclos.idCurso = cursos.id
                INNER JOIN materias ON materias.idCurso = cursos.id
                WHERE materias.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $idMateria);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_free_result($result);
    return $fila ? intval($fila['id']) : 0;
}

// Horas anuales de una materia (v3 obtenerHorasAnualesPorMateria)
function temas_horas_anuales($db, $idMateria) {
    $stmt = mysqli_prepare($db, "SELECT horas_anuales FROM materias WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idMateria);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_free_result($result);
    return $fila ? intval($fila['horas_anuales']) : 0;
}

// Checkbox de competencias de una materia (v3 generarCheckboxesCompetenciasMateria)
function temas_competencias_materia($db, $idMateria, $idCiclo) {
    $competencias = [];
    $yaAgregados = [];

    // Tipo 1: asignadas a la materia
    $stmt = mysqli_prepare($db, "SELECT cc.id, cc.codigo, cc.texto, cc.tipo, cc.orden
                FROM competencias_ciclos cc
                INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
                WHERE cm.idMateria = ? AND cc.tipo = 1
                ORDER BY cc.orden");
    mysqli_stmt_bind_param($stmt, "i", $idMateria);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($fila = mysqli_fetch_assoc($r)) {
        $competencias[] = $fila;
        $yaAgregados[intval($fila['id'])] = true;
    }
    mysqli_stmt_close($stmt);
    mysqli_free_result($r);

    // Tipo 2: del ciclo (siempre)
    if ($idCiclo > 0) {
        $stmt = mysqli_prepare($db, "SELECT id, codigo, texto, tipo, orden
                    FROM competencias_ciclos
                    WHERE idCiclo = ? AND tipo = 2
                    ORDER BY orden");
        mysqli_stmt_bind_param($stmt, "i", $idCiclo);
        mysqli_stmt_execute($stmt);
        $r = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($r)) {
            $id = intval($fila['id']);
            if (!isset($yaAgregados[$id])) {
                $competencias[] = $fila;
                $yaAgregados[$id] = true;
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_free_result($r);
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
            $stmt = mysqli_prepare($db, "SELECT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales
                        FROM materias m
                        LEFT JOIN cursos c ON c.id = m.idCurso
                        WHERE m.tiene_programacion = 1
                        ORDER BY c.orden, c.nombre, m.nombre");
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $materias = [];
            while ($fila = mysqli_fetch_assoc($r)) {
                $idMateria = intval($fila['id']);
                $materias[] = [
                    'id' => $idMateria,
                    'materia' => $fila['materia'],
                    'curso' => $fila['curso'],
                    'horas_anuales' => intval($fila['horas_anuales']),
                    'idCiclo' => temas_id_ciclo_por_materia($db, $idMateria)
                ];
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);
            temas_salida(['success' => true, 'data' => $materias]);

        // --------------------------------------------------------------------
        // Listar temas de una materia (como v3 mostrarTemasPorMateria)
        // ------------------------------------------------------------------
        } elseif ($action === 'listar') {
            $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }
            $stmt = mysqli_prepare($db, "SELECT id, orden, titulo, horas, peso_evaluacion
                        FROM temas WHERE idMateria = ? ORDER BY orden");
            mysqli_stmt_bind_param($stmt, "i", $idMateria);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $temas = [];
            while ($fila = mysqli_fetch_assoc($r)) {
                $temas[] = [
                    'id' => intval($fila['id']),
                    'orden' => intval($fila['orden']),
                    'titulo' => $fila['titulo'],
                    'horas' => intval($fila['horas']),
                    'peso_evaluacion' => intval($fila['peso_evaluacion'])
                ];
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);
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
            $stmt = mysqli_prepare($db, "SELECT * FROM temas WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $idTema);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $fila = mysqli_fetch_assoc($r);
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);
            if (!$fila) {
                temas_salida(['success' => false, 'error' => 'Tema no encontrado'], 404);
            }

            $idTemaP = $idTema;
            $stmt = mysqli_prepare($db, "SELECT idRA, codigo FROM criterios_temas WHERE idTema = ?");
            mysqli_stmt_bind_param($stmt, "i", $idTemaP);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $criterios = [];
            while ($c = mysqli_fetch_assoc($r)) {
                $criterios[] = ['idRA' => intval($c['idRA']), 'codigo' => $c['codigo']];
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);

            $idTemaP2 = $idTema;
            $stmt = mysqli_prepare($db, "SELECT idCompetencia FROM competencias_temas WHERE idTema = ?");
            mysqli_stmt_bind_param($stmt, "i", $idTemaP2);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $competencias = [];
            while ($c = mysqli_fetch_assoc($r)) {
                $competencias[] = intval($c['idCompetencia']);
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);

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

            $stmt = mysqli_prepare($db, "SELECT id, orden, texto, porcentaje_evaluacion, es_clave
                        FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden");
            mysqli_stmt_bind_param($stmt, "i", $idMateria);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $ra = [];
            $total = 0;
            while ($fila = mysqli_fetch_assoc($r)) {
                $idRA = intval($fila['id']);
                $total += intval($fila['porcentaje_evaluacion']);

                $ceStmt = mysqli_prepare($db, "SELECT codigo, texto FROM criterios_evaluacion WHERE idRA = ? ORDER BY codigo");
                mysqli_stmt_bind_param($ceStmt, "i", $idRA);
                mysqli_stmt_execute($ceStmt);
                $cr = mysqli_stmt_get_result($ceStmt);
                $ce = [];
                while ($c = mysqli_fetch_assoc($cr)) {
                    $ce[] = ['idRA' => $idRA, 'codigo' => $c['codigo'], 'texto' => $c['texto']];
                }
                mysqli_stmt_close($ceStmt);
                mysqli_free_result($cr);

                $ra[] = [
                    'id' => $idRA,
                    'orden' => intval($fila['orden']),
                    'texto' => $fila['texto'],
                    'porcentaje_evaluacion' => intval($fila['porcentaje_evaluacion']),
                    'es_clave' => intval($fila['es_clave']),
                    'ce' => $ce
                ];
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);

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

            $stmt = mysqli_prepare($db, "INSERT INTO temas
                        (idMateria, orden, titulo, horas, trimestre, peso_evaluacion,
                         descripcion, justificacion, contexto, contenidos, secuenciacion,
                         recursos, evaluacion, metodologia, adaptaciones,
                         contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                        VALUES (?, ?, '', 0, 0, 0, '', '', '', '', '', '', '', '', '', 1, 1, 1, 1)");
            // Nota: titulo se inserta como '' aquí; se actualiza a continuación para respetar el texto.
            mysqli_stmt_bind_param($stmt, "ii", $idMateria, $orden);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                temas_salida(['success' => false, 'error' => 'Error al crear el tema: ' . mysqli_error($db)], 500);
            }
            $nuevoId = mysqli_insert_id($db);
            mysqli_stmt_close($stmt);

            $upd = mysqli_prepare($db, "UPDATE temas SET titulo = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "si", $titulo, $nuevoId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);

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

            mysqli_begin_transaction($db);

            $upd = mysqli_prepare($db, "UPDATE temas SET
                        orden = ?, titulo = ?, horas = ?, trimestre = ?, peso_evaluacion = ?,
                        descripcion = ?, justificacion = ?, contexto = ?, contenidos = ?,
                        secuenciacion = ?, recursos = ?, evaluacion = ?, metodologia = ?, adaptaciones = ?,
                        contexto_defecto = ?, recursos_defecto = ?, metodologia_defecto = ?, adaptaciones_defecto = ?
                        WHERE id = ?");
            mysqli_stmt_bind_param($upd, "isiisssssssssssssi",
                $orden, $titulo, $horas, $trimestre, $peso,
                $descripcion, $justificacion, $contexto, $contenidos,
                $secuenciacion, $recursos, $evaluacion, $metodologia, $adaptaciones,
                $contextoDefecto, $recursosDefecto, $metodologiaDefecto, $adaptacionesDefecto,
                $idTema);
            $ok = mysqli_stmt_execute($upd);
            $afectados = mysqli_affected_rows($db);
            mysqli_stmt_close($upd);

            if ($ok) {
                // Reemplazar criterios de evaluación (CE)
                $del = mysqli_prepare($db, "DELETE FROM criterios_temas WHERE idTema = ?");
                mysqli_stmt_bind_param($del, "i", $idTema);
                mysqli_stmt_execute($del);
                mysqli_stmt_close($del);

                $insCE = mysqli_prepare($db, "INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)");
                $criterios = isset($body['criterios']) && is_array($body['criterios']) ? $body['criterios'] : [];
                foreach ($criterios as $ce) {
                    if (!is_array($ce) || !isset($ce['idRA'], $ce['codigo'])) {
                        continue;
                    }
                    $idRA = intval($ce['idRA']);
                    $codigo = $ce['codigo'];
                    $idTemaIns = $idTema;
                    mysqli_stmt_bind_param($insCE, "isi", $idRA, $codigo, $idTemaIns);
                    mysqli_stmt_execute($insCE);
                }
                mysqli_stmt_close($insCE);

                // Reemplazar competencias
                $del2 = mysqli_prepare($db, "DELETE FROM competencias_temas WHERE idTema = ?");
                mysqli_stmt_bind_param($del2, "i", $idTema);
                mysqli_stmt_execute($del2);
                mysqli_stmt_close($del2);

                $insCOM = mysqli_prepare($db, "INSERT INTO competencias_temas (idCompetencia, idTema) VALUES (?, ?)");
                $competencias = isset($body['competencias']) && is_array($body['competencias']) ? $body['competencias'] : [];
                foreach ($competencias as $com) {
                    $idCom = intval($com);
                    $idTemaIns = $idTema;
                    mysqli_stmt_bind_param($insCOM, "ii", $idCom, $idTemaIns);
                    mysqli_stmt_execute($insCOM);
                }
                mysqli_stmt_close($insCOM);

                mysqli_commit($db);
            } else {
                mysqli_rollback($db);
                mysqli_stmt_close($upd);
                temas_salida(['success' => false, 'error' => 'Error al guardar el tema: ' . mysqli_error($db)], 500);
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

            mysqli_begin_transaction($db);
            foreach (['competencias_temas', 'criterios_temas', 'programaciones_aula_temas'] as $tabla) {
                $del = mysqli_prepare($db, "DELETE FROM {$tabla} WHERE idTema = ?");
                mysqli_stmt_bind_param($del, "i", $idTema);
                mysqli_stmt_execute($del);
                mysqli_stmt_close($del);
            }
            $delTema = mysqli_prepare($db, "DELETE FROM temas WHERE id = ?");
            mysqli_stmt_bind_param($delTema, "i", $idTema);
            if (!mysqli_stmt_execute($delTema)) {
                mysqli_rollback($db);
                mysqli_stmt_close($delTema);
                temas_salida(['success' => false, 'error' => 'Error al borrar el tema: ' . mysqli_error($db)], 500);
            }
            mysqli_stmt_close($delTema);
            mysqli_commit($db);

            temas_salida(['success' => true, 'message' => 'Tema eliminado correctamente']);

        // ----------------------------------------------------------------------
        // Recalcular porcentajes de evaluación de los RA (v3 calcularPorcentajesRA)
        // ----------------------------------------------------------------------
        } elseif ($action === 'recalcular_porcentajes') {
            $idMateria = isset($body['idMateria']) ? intval($body['idMateria']) : 0;
            if ($idMateria <= 0) {
                temas_salida(['success' => false, 'error' => 'Debe indicar una materia'], 400);
            }

            $stmt = mysqli_prepare($db, "SELECT ra.id, ra.orden, COUNT(ct.codigo) AS num_criterios
                        FROM resultados_aprendizaje ra
                        LEFT JOIN criterios_temas ct ON ra.id = ct.idRA
                        WHERE ra.idMateria = ?
                        GROUP BY ra.id, ra.orden
                        ORDER BY ra.orden");
            mysqli_stmt_bind_param($stmt, "i", $idMateria);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
            $listadoRA = [];
            while ($fila = mysqli_fetch_assoc($r)) {
                $listadoRA[] = ['id' => intval($fila['id']), 'num_criterios' => intval($fila['num_criterios'])];
            }
            mysqli_stmt_close($stmt);
            mysqli_free_result($r);

            if (!empty($listadoRA)) {
                $stmt2 = mysqli_prepare($db, "SELECT COUNT(*) AS total
                            FROM resultados_aprendizaje ra
                            INNER JOIN criterios_temas ct ON ra.id = ct.idRA
                            WHERE ra.idMateria = ?");
                mysqli_stmt_bind_param($stmt2, "i", $idMateria);
                mysqli_stmt_execute($stmt2);
                $r2 = mysqli_stmt_get_result($stmt2);
                $filaTotal = mysqli_fetch_assoc($r2);
                mysqli_stmt_close($stmt2);
                mysqli_free_result($r2);
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

            $upd = mysqli_prepare($db, "UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ? WHERE id = ?");
            foreach ($porcentajes as $p) {
                $porcentaje = $p['porcentaje'];
                $idRA = $p['id'];
                mysqli_stmt_bind_param($upd, "ii", $porcentaje, $idRA);
                mysqli_stmt_execute($upd);
            }
            mysqli_stmt_close($upd);

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

            $upd = mysqli_prepare($db, "UPDATE temas SET evaluacion = ? WHERE idMateria = ?");
            mysqli_stmt_bind_param($upd, "si", $evaluacion, $idMateria);
            if (!mysqli_stmt_execute($upd)) {
                mysqli_stmt_close($upd);
                temas_salida(['success' => false, 'error' => 'Error al copiar la evaluación: ' . mysqli_error($db)], 500);
            }
            $afectados = mysqli_affected_rows($db);
            mysqli_stmt_close($upd);

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

            $upd = mysqli_prepare($db, "UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ?, es_clave = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "iii", $porcentaje, $esClave, $idRA);
            if (!mysqli_stmt_execute($upd)) {
                mysqli_stmt_close($upd);
                temas_salida(['success' => false, 'error' => 'Error al actualizar el RA: ' . mysqli_error($db)], 500);
            }
            mysqli_stmt_close($upd);

            temas_salida(['success' => true, 'message' => 'Resultado de aprendizaje actualizado']);

        } else {
            temas_salida(['success' => false, 'error' => 'Acción no válida'], 400);
        }

    } else {
        temas_salida(['success' => false, 'error' => 'Método no permitido'], 405);
    }

} catch (Exception $e) {
    closeDBConnection($db);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
