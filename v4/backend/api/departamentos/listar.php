<?php
// API endpoint para cargar todos los departamentos
// Devuelve un array JSON con los departamentos ordenados por nombre

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $departamentos = $db->fetchAll("SELECT * FROM departamentos ORDER BY nombre");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($departamentos);
?>
