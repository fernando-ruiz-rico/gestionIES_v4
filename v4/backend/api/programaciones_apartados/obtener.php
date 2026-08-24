<?php
// API endpoint para obtener un apartado específico
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = getOptimoInt('id');

if ($id <= 0) {
    sendJSONError('ID no válido', 400);
}

try {
    $db = Db::open();
    $fila = $db->fetchOne("SELECT * FROM apartados_programaciones WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$fila) {
    sendJSONError('Apartado no encontrado', 404);
}

sendJSONSuccess($fila);
?>
