<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        exit;
    }
    
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM apartados_programaciones WHERE id = ?");
    $stmt->execute([$id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'data' => $resultado]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Apartado no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener el apartado: ' . $e->getMessage()]);
}
?>
