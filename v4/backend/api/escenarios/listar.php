<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    $escenarios = $db->fetchAll("SELECT id, nombre, actual, activo_desideratas, modo_rueda FROM escenarios_desideratas ORDER BY nombre");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($escenarios);
?>
