<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $especialidades = $db->fetchAll("SELECT e.*, d.nombre as departamento FROM especialidades e LEFT JOIN departamentos d ON e.idDepartamento = d.id ORDER BY e.descripcion");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($especialidades);
?>
