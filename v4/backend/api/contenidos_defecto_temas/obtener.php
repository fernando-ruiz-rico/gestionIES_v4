<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
if (!isset($_GET['id'])) { echo json_encode(['success' => false, 'error' => 'ID requerido']); exit; }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM contenidos_defecto_temas WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
echo $data ? json_encode(['success' => true, 'data' => $data]) : json_encode(['success' => false, 'error' => 'No encontrado']);
?>
