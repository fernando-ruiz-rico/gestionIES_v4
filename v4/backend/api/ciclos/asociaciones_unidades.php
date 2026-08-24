<?php
// API para listar las unidades de competencia asociadas a un ciclo y las
// disponibles para asociar (Fase 1).
// Equivalente a v3/ajax/ciclos/cargar_asociaciones_unidades.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idCiclo = isset($_GET['idCiclo']) ? intval($_GET['idCiclo']) : 0;
if ($idCiclo <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    // Unidades ya asociadas al ciclo
    $asociadas = $db->fetchAll("SELECT uc.codigo, uc.texto
                                FROM unidades_ciclos u
                                INNER JOIN unidades_competencia uc ON uc.codigo = u.codigoUnidad
                                WHERE u.idCiclo = ?
                                ORDER BY uc.codigo", $idCiclo);

    // Unidades que aún no están asociadas a este ciclo
    $disponibles = $db->fetchAll("SELECT uc.codigo, uc.texto
                                   FROM unidades_competencia uc
                                   WHERE NOT EXISTS (
                                       SELECT 1 FROM unidades_ciclos u
                                       WHERE u.codigoUnidad = uc.codigo AND u.idCiclo = ?
                                   )
                                   ORDER BY uc.codigo", $idCiclo);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('asociadas' => $asociadas, 'disponibles' => $disponibles));
?>
