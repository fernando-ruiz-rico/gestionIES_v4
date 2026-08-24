<?php
// API endpoint para cargar todos los departamentos
// Devuelve un array JSON con los departamentos ordenados por nombre

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $departamentos = $db->fetchAll("SELECT * FROM departamentos ORDER BY nombre");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($departamentos);
?>
