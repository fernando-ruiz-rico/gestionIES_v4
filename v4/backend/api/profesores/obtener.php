<?php
// API endpoint para cargar un profesor específico por ID
// Devuelve: objeto JSON con los datos del profesor

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de profesor no proporcionado']);
    exit;
}

$id = intval($_GET['id']);
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $db = new Db($conn);
    $profesor = $db->fetchOne("SELECT * FROM profesores WHERE id = $id");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$profesor) {
    http_response_code(404);
    echo json_encode(['error' => 'Profesor no encontrado']);
    exit;
}

echo json_encode($profesor);
?>
