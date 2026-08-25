<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// lista las cualificaciones profesionales
// Fiel a v3: las cualificaciones profesionales (cualificaciones_profesionales)
// pueden asociar unidades de competencia (unidades_competencia) a través de
// la tabla cualificaciones_unidades.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $sql = "SELECT codigo, texto FROM cualificaciones_profesionales ORDER BY codigo";
    $cualificaciones = $db->fetchAll($sql);
    $db->close();
    sendJSONSuccess($cualificaciones);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
