<?php
// API para listar los cursos (Fase 1)
// Equivalente a v3/ajax/cursos/cargar_cursos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();
    $cursos = $db->fetchAll("SELECT * FROM cursos ORDER BY orden, nombre");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($cursos);
?>
