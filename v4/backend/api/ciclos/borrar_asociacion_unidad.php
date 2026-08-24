<?php
// API para desasociar una unidad de competencia de un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_asociacion.php
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
    $afectadas = $db->execute("DELETE FROM unidades_ciclos WHERE idCiclo = ? AND codigoUnidad = ?", $idCiclo, $CodigoUnidad);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas > 0) {
    sendJSONSuccess(null, 'Asociación eliminada');
}

sendJSONError('La asociación no existe', 404);
?>
