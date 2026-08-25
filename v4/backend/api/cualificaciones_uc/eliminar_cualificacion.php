<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// elimina una cualificación (solo si no tiene UC asociadas)
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
        throw new Exception('Código de cualificación inválido');
    }

    $fila = $db->fetchOne("SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoCualificacion=?", $codigo);
    if ($fila['total'] > 0) {
        $db->close();
        sendJSONError('La cualificación tiene unidades de competencia asociadas');
    }

    $db->execute("DELETE FROM cualificaciones_profesionales WHERE codigo=?", $codigo);
    $db->close();
    sendJSONSuccess(null, 'Cualificación eliminada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
