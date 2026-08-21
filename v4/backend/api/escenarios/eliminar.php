<?php
// API para eliminar un escenario (tabla real: escenarios_desideratas)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$id = isset($datos['id']) ? intval($datos['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$stmt = mysqli_prepare($db, "DELETE FROM escenarios_desideratas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$afectados = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

if ($afectados === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Escenario eliminado']);
?>
