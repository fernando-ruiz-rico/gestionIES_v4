<?php
// API para listar los apartados del PCCF (Fase 3.2 - Apartados PCCF)
// Devuelve el listado de apartados ordenados por su posición en la tabla.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

try {
    $db = Db::open();

    $apartados = $db->fetchAll("SELECT * FROM apartados_pccf ORDER BY orden");
    sendJSONSuccess($apartados);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
