<?php
// API para asociar una unidad de competencia a un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/nueva_asociacion.php
require_once '../../config.php';
cabeceraJson();

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
$idCiclo      = datosOptimoInt($datos, 'idCiclo');
$CodigoUnidad = trim(datosOptimo($datos, 'codigoUnidad'));
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
