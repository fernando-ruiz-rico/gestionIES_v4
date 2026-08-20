<?php
// API endpoint para obtener un apartado específico
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM apartados_programaciones WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($result);

if ($fila) {
    echo json_encode(['success' => true, 'data' => $fila]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Apartado no encontrado']);
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
