<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// actualiza el % de evaluación y si es un RA clave
require_once '../../config.php';
require_once '../../lib/resultados_aprendizaje.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    $idResultado = intval($datos['idResultado']);
    $porcentajeEvaluacion = datosOptimoInt($datos, 'porcentaje_evaluacion');
    $esClave = isset($datos['es_clave']) ? 1 : 0;
    if ($idResultado <= 0) {
        throw new Exception('ID de resultado inválido');
    }

    raComprobarDepartamento(raIdDepartamentoDeRA($db, $idResultado));

    $db->execute(
        "UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ?, es_clave = ? WHERE id = ?",
        $porcentajeEvaluacion, $esClave, $idResultado);
    $db->close();
    sendJSONSuccess(null, 'Evaluación actualizada');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
