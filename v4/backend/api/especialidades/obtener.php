<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = trim(isset($_GET['id']) ? $_GET['id'] : '');
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $db = Db::open();
    $especialidad = $db->fetchOne("SELECT e.*, d.nombre as departamento FROM especialidades e LEFT JOIN departamentos d ON e.idDepartamento = d.id WHERE e.id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if (!$especialidad) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode($especialidad);
?>
