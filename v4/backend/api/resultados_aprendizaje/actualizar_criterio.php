<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// actualiza un criterio de evaluación
require_once '../../config.php';
require_once '../../lib/resultados_aprendizaje.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    if (!raTienePermisoEdicion()) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    $idResultado = intval($datos['idResultado']);
    $codigoAntiguo = $datos['codigo'];
    $nuevoCodigo = $datos['nuevoCodigo'];
    $nuevoTexto = $datos['nuevoTexto'] === null ? '' : $datos['nuevoTexto'];
    if ($idResultado <= 0 || empty($codigoAntiguo)) {
        throw new Exception('Datos incompletos para actualizar el criterio');
    }

    raComprobarDepartamento(raIdDepartamentoDeRA($db, $idResultado));

    $db->execute(
        "UPDATE criterios_evaluacion SET codigo = ?, texto = ? WHERE idRA = ? AND codigo = ?",
        $nuevoCodigo, $nuevoTexto, $idResultado, $codigoAntiguo);
    $db->close();
    sendJSONSuccess(null, 'Criterio de evaluación actualizado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
