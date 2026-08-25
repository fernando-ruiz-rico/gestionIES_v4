<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// disocia una unidad de una cualificación
// Fiel a v3: las cualificaciones profesionales (cualificaciones_profesionales)
// pueden asociar unidades de competencia (unidades_competencia) a través de
// la tabla cualificaciones_unidades.
// Permisos: solo el rol admin.
require_once '../../config.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    // Permiso: solo admin
    checkPermission(array(ROLE_ADMIN));

    $codigoCualificacion = datosOptimo($datos, 'codigoCualificacion');
    $codigoUnidad = datosOptimo($datos, 'codigoUnidad');
    if (empty($codigoCualificacion) || empty($codigoUnidad)) {
        throw new Exception('Datos incompletos para disociar la unidad');
    }

    $db->execute("DELETE FROM cualificaciones_unidades WHERE codigoCualificacion=? AND codigoUnidad=?", $codigoCualificacion, $codigoUnidad);
    $db->close();
    sendJSONSuccess(null, 'Unidad de competencia desasociada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
