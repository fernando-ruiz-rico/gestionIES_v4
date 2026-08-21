<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$id = intval(isset($datos['id']) ? $datos['id'] : 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$stmt = mysqli_prepare($db, "DELETE FROM grupos WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
