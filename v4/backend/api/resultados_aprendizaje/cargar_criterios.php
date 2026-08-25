<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// carga los criterios de evaluación asociados a un resultado
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $idResultado = getOptimoInt('idResultado');
    if ($idResultado <= 0) {
        throw new Exception('ID de resultado inválido');
    }

    $criterios = $db->fetchAll("SELECT * FROM criterios_evaluacion WHERE idRA = ? ORDER BY codigo", $idResultado);
    $db->close();
    sendJSONSuccess($criterios);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
