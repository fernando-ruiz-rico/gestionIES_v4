<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// elimina una unidad de competencia (solo si no está asociada)
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

    $codigo = datosOptimo($datos, 'codigo');
    if (empty($codigo)) {
        throw new Exception('Código de unidad inválido');
    }

    $fila = $db->fetchOne("SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoUnidad=?", $codigo);
    if ($fila['total'] > 0) {
        $db->close();
        sendJSONError('La unidad está asociada a alguna cualificación');
    }

    $db->execute("DELETE FROM unidades_competencia WHERE codigo=?", $codigo);
    $db->close();
    sendJSONSuccess(null, 'Unidad de competencia eliminada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
