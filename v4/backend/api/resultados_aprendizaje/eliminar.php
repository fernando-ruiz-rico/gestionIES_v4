<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// elimina un resultado de aprendizaje (y sus criterios de evaluación)
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

    $id = intval($datos['id']);
    if ($id <= 0) {
        throw new Exception('ID de resultado inválido');
    }

    raComprobarDepartamento(raIdDepartamentoDeRA($db, $id));

    $db->execute("DELETE FROM criterios_evaluacion WHERE idRA = ?", $id);
    $db->execute("DELETE FROM resultados_aprendizaje WHERE id = ?", $id);
    $db->close();
    sendJSONSuccess(null, 'Resultado de aprendizaje eliminado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
