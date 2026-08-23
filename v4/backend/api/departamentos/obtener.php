<?php
// API endpoint para cargar un departamento específico por ID
// Devuelve un objeto JSON con los datos del departamento

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

try {
    $db = Db::open();
    $departamento = $db->fetchOne("SELECT * FROM departamentos WHERE id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$departamento) {
    http_response_code(404);
    echo json_encode(['error' => 'Departamento no encontrado']);
    exit;
}

echo json_encode($departamento);
?>
