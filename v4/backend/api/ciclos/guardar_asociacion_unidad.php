<?php
// API para asociar una unidad de competencia a un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/nueva_asociacion.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$idCiclo      = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;
$CodigoUnidad = isset($datos['codigoUnidad']) ? trim($datos['codigoUnidad']) : '';
if ($idCiclo <= 0 || $CodigoUnidad === '') {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();
    $db->execute("INSERT IGNORE INTO unidades_ciclos (idCiclo, codigoUnidad) VALUES (?, ?)", $idCiclo, $CodigoUnidad);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null, 'Unidad asociada al ciclo');
?>
