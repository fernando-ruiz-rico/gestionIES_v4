<?php
// API de selección (Desideratas): quitar una selección concreta
// (v3/borrar_seleccion.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$datos = cuerpoJson();
if ($datos === null) {
    sendJSONError('Faltan datos', 400);
}
$id = datosOptimoInt($datos, 'id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}
try {
    $db = Db::open();
    $afectadas = $db->execute("DELETE FROM seleccion WHERE id = ?", $id);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
if ($afectadas === 0) {
    sendJSONError('No encontrado', 404);
}
sendJSONSuccess(null, 'Eliminada');
