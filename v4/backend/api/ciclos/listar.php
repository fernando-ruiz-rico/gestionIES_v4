<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $ciclos = $db->fetchAll("SELECT * FROM ciclos ORDER BY nombre");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($ciclos);
?>
