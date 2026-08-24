<?php
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    $especialidades = $db->fetchAll("SELECT e.*, d.nombre as departamento FROM especialidades e LEFT JOIN departamentos d ON e.idDepartamento = d.id ORDER BY e.descripcion");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($especialidades);
?>
