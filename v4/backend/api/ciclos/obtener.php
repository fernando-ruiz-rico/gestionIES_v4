<?php
// API para obtener un ciclo formativo por su id (Fase 1)
// Equivalente a v3/ajax/ciclos/cargar_ciclo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idCiclo = getOptimoInt('id');
if ($idCiclo <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $ciclo = $db->fetchOne("SELECT id, nombre, familia, nivel FROM ciclos WHERE id = ?", $idCiclo);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$ciclo) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($ciclo);
?>
