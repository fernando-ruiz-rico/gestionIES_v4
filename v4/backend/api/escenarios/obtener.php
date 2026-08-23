<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    $escenario = $db->fetchOne("SELECT id, nombre, actual, activo_desideratas, modo_rueda FROM escenarios_desideratas WHERE id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$escenario) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode($escenario);
?>
