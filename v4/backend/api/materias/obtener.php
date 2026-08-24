<?php
require_once '../../config.php';
cabeceraJson();

$id = getOptimoInt('id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $materia = $db->fetchOne("SELECT * FROM materias WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$materia) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($materia);
?>
