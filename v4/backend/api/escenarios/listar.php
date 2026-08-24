<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    $escenarios = $db->fetchAll("SELECT id, nombre, actual, activo_desideratas, modo_rueda FROM escenarios_desideratas ORDER BY nombre");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($escenarios);
?>
