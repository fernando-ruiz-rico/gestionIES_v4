<?php
// API endpoint para cargar un departamento específico por ID
// Devuelve un objeto JSON con los datos del departamento

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['id'])) {
    sendJSONError('ID de departamento no proporcionado', 400);
}

$id = intval($_GET['id']);

try {
    $db = Db::open();
    $departamento = $db->fetchOne("SELECT * FROM departamentos WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$departamento) {
    sendJSONError('Departamento no encontrado', 404);
}

sendJSONSuccess($departamento);
?>
