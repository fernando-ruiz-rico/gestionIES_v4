<?php
// API para obtener un ciclo formativo por su id (Fase 1)
// Equivalente a v3/ajax/ciclos/cargar_ciclo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idCiclo = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idCiclo <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $db = Db::open();
    $ciclo = $db->fetchOne("SELECT id, nombre, familia, nivel FROM ciclos WHERE id = ?", $idCiclo);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$ciclo) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode($ciclo);
?>
