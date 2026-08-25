<?php
// FASE 2.6 — Listar temas de una materia (+ horas anuales para los totales).
// (v3 mostrarTemasPorMateria)
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

    $temas = array();
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

    $horasAnuales = temas_horas_anuales($db, $idMateria);
    $db->close();
    sendJSONSuccess([
        'temas' => $temas,
        'horas_anuales' => $horasAnuales
    ]);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
