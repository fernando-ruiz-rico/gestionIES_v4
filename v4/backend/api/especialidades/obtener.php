<?php
// API endpoint para obtener una especialidad específica
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de especialidad inválido']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM especialidades WHERE idEspecialidad = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$especialidad = mysqli_fetch_assoc($result);

if (!$especialidad) {
    http_response_code(404);
    echo json_encode(['error' => 'Especialidad no encontrada']);
    exit;
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode($especialidad);
?>
