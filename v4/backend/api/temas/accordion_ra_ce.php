<?php
// FASE 2.6 — Acordeón RA/CE + checkboxes de competencias (nivel materia).
require_once '../../config.php';
require_once '../../lib/temas.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $idMateria = getOptimoInt('idMateria');
    if ($idMateria <= 0) {
        throw new Exception('Debe indicar una materia');
    }

    $idCiclo = temas_id_ciclo_por_materia($db, $idMateria);

    $ra = array();
    $total = 0;
    foreach ($db->fetchAll("SELECT id, orden, texto, porcentaje_evaluacion, es_clave
                    FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", $idMateria) as $fila) {
        $idRA = intval($fila['id']);
        $total += intval($fila['porcentaje_evaluacion']);

        $ce = array();
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
    $db->close();

    sendJSONSuccess([
        'idCiclo' => $idCiclo,
        'ra' => $ra,
        'total' => $total,
        'competencias' => $competencias
    ]);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
