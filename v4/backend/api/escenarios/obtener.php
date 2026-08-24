<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    $escenario = $db->fetchOne("SELECT id, nombre, actual, activo_desideratas, modo_rueda FROM escenarios_desideratas WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$escenario) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($escenario);
?>
