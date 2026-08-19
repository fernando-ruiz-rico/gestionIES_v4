<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        exit;
    }
    
    $id = intval($_GET['id']);
    
    // Borrado lógico
    $stmt = $pdo->prepare("UPDATE apartados_programaciones SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Apartado eliminado correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>
