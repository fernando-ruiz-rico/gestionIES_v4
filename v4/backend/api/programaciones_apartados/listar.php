<?php
// API endpoint para listar apartados de programaciones
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    $apartados = $db->fetchAll("SELECT * FROM apartados_programaciones ORDER BY orden");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($apartados);
?>
