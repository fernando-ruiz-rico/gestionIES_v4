<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// elimina un criterio de evaluación
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
    $codigo = $datos['codigo'];
    if ($idResultado <= 0 || empty($codigo)) {
        throw new Exception('Datos incompletos para eliminar el criterio');
    }

    raComprobarDepartamento(raIdDepartamentoDeRA($db, $idResultado));

    $db->execute("DELETE FROM criterios_evaluacion WHERE idRA = ? AND codigo = ?", $idResultado, $codigo);
    $db->close();
    sendJSONSuccess(null, 'Criterio de evaluación eliminado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
