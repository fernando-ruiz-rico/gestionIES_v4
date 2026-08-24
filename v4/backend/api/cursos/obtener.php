<?php
// API para obtener un curso por su id (Fase 1)
// Equivalente a v3 (formulario de edición)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $curso = $db->fetchOne("SELECT * FROM cursos WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$curso) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($curso);
?>
