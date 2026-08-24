<?php
// API para listar los ciclos formativos disponibles (Fase 3.1 - PCCF)
// Devuelve el listado de ciclos para el selector del PCCF

require_once '../../config.php';
cabeceraJson();

$db = Db::open();

try {
    $ciclos = $db->fetchAll("SELECT * FROM ciclos ORDER BY nombre");
    sendJSONSuccess($ciclos);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
