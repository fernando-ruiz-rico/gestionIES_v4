<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = getOptimoInt('id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $grupo = $db->fetchOne("SELECT * FROM grupos WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$grupo) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($grupo);
?>
