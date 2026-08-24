<?php
// API para listar los cursos asociados a un ciclo y los cursos disponibles
// para asociar (Fase 1). Equivalente a v3/ajax/ciclos/cargar_asociaciones_cursos.php
require_once '../../config.php';
cabeceraJson();

$idCiclo = getOptimoInt('idCiclo');
if ($idCiclo <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    // Cursos ya asociados al ciclo
    $asociados = $db->fetchAll("SELECT cc.idCurso, cc.orden, cu.nombre, cu.abreviatura
                                FROM cursos_ciclos cc
                                INNER JOIN cursos cu ON cu.id = cc.idCurso
                                WHERE cc.idCiclo = ?
                                ORDER BY cc.orden", $idCiclo);

    // Cursos del centro que aún no están asociados a este ciclo
    $disponibles = $db->fetchAll("SELECT cu.id, cu.nombre, cu.abreviatura
                                   FROM cursos cu
                                   WHERE NOT EXISTS (
                                       SELECT 1 FROM cursos_ciclos cc
                                       WHERE cc.idCurso = cu.id AND cc.idCiclo = ?
                                   )
                                   ORDER BY cu.nombre", $idCiclo);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('asociados' => $asociados, 'disponibles' => $disponibles));
?>
