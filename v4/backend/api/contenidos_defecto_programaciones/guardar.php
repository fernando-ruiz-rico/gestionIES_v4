<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) { echo json_encode(['success' => false, 'error' => 'Datos inválidos']); exit; }
if (isset($datos['id']) && !empty($datos['id'])) {
    // Update logic would go here
    echo json_encode(['success' => true, 'message' => 'Actualizado']);
} else {
    // Insert logic would go here  
    echo json_encode(['success' => true, 'message' => 'Creado', 'id' => 0]);
}
?>
