<?php
// API para obtener un curso por su id (Fase 1)
// Equivalente a v3 (formulario de edición)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $db = Db::open();
    $curso = $db->fetchOne("SELECT * FROM cursos WHERE id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$curso) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode($curso);
?>
