<?php
// API endpoint para eliminar especialidades
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$idEspecialidad = isset($datos['idEspecialidad']) ? intval($datos['idEspecialidad']) : 0;

if ($idEspecialidad <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de especialidad inválido']);
    exit;
}

try {
    $stmt = mysqli_prepare($db, "DELETE FROM especialidades WHERE idEspecialidad = ?");
    mysqli_stmt_bind_param($stmt, "i", $idEspecialidad);
    $exito = mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Especialidad no encontrada o ya eliminada']);
        exit;
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    
    echo json_encode(['success' => true, 'message' => 'Especialidad eliminada correctamente']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>
