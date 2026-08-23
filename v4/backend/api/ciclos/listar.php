<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $ciclos = $db->fetchAll("SELECT * FROM ciclos ORDER BY nombre");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($ciclos);
?>
