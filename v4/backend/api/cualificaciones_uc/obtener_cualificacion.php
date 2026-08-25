<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// devuelve una cualificación
// Fiel a v3: las cualificaciones profesionales (cualificaciones_profesionales)
// pueden asociar unidades de competencia (unidades_competencia) a través de
// la tabla cualificaciones_unidades.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $codigo = getOptimo('codigo');
    if (empty($codigo)) {
        throw new Exception('Código de cualificación inválido');
    }

    $fila = $db->fetchOne("SELECT * FROM cualificaciones_profesionales WHERE codigo=?", $codigo);
    if (!$fila) {
        $db->close();
        sendJSONError('Cualificación no encontrada', 404);
    }
    $db->close();
    sendJSONSuccess($fila);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
