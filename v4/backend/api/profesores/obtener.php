<?php
// API endpoint para cargar un profesor específico por ID
// Devuelve: objeto JSON con los datos del profesor

require_once '../../config.php';
cabeceraJson();

if (empty($_GET['id'])) {
    sendJSONError('ID de profesor no proporcionado', 400);
}

$id = intval($_GET['id']);

try {
    $db = Db::open();
    $profesor = $db->fetchOne("SELECT * FROM profesores WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error al consultar la base de datos: ' . $e->getMessage(), 500);
}

if (!$profesor) {
    sendJSONError('Profesor no encontrado', 404);
}

sendJSONSuccess($profesor);
?>
