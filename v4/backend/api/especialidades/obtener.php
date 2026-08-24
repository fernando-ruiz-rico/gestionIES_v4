<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$id = trim(isset($_GET['id']) ? $_GET['id'] : '');
if (empty($id)) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $especialidad = $db->fetchOne("SELECT e.*, d.nombre as departamento FROM especialidades e LEFT JOIN departamentos d ON e.idDepartamento = d.id WHERE e.id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$especialidad) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess($especialidad);
?>
