<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
try {
    $stmt = $pdo->query("SELECT * FROM pccf_apartados WHERE activo = 1 ORDER BY orden, id");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
