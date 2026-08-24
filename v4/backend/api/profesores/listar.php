<?php
// API endpoint para cargar todos los profesores de un departamento
// Recibe: idDepartamento (requerido)
// Devuelve: array JSON con los profesores ordenados por campo 'orden'

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['idDepartamento'])) {
    sendJSONError('ID de departamento no proporcionado', 400);
}

$idDepartamento = intval($_GET['idDepartamento']);

try {
    $db = Db::open();
    $profesores = $db->fetchAll("SELECT * FROM profesores WHERE idDepartamento = ? ORDER BY orden", $idDepartamento);
} catch (DbException $e) {
    sendJSONError('Error al consultar la base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($profesores);
?>
