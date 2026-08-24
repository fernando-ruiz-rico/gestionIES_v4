<?php
// API para obtener los datos de un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Devuelve en formato JSON los datos del apartado solicitado.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idApartado = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idApartado <= 0) {
    sendJSONError('Apartado no válido', 400);
}

try {
    $db = Db::open();

    $apartado = $db->fetchOne("SELECT * FROM apartados_pccf WHERE id = ?", $idApartado);
    if ($apartado !== null) {
        sendJSONSuccess($apartado);
    } else {
        sendJSONError('Apartado no encontrado', 404);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
