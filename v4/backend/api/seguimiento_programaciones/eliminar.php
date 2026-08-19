<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
if (!isset($_GET['id'])) { echo json_encode(['success' => false, 'error' => 'ID requerido']); exit; }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("UPDATE seguimiento_programaciones SET activo = 0 WHERE id = ?");
$stmt->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Eliminado']);
?>
