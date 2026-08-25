<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// lista las unidades asociadas a una cualificación
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

    $sql = "SELECT cu.codigoUnidad, uc.texto AS texto
            FROM cualificaciones_unidades cu
            JOIN unidades_competencia uc ON uc.codigo = cu.codigoUnidad
            WHERE cu.codigoCualificacion=?
            ORDER BY cu.codigoUnidad";
    $asociaciones = $db->fetchAll($sql, $codigo);
    $db->close();
    sendJSONSuccess($asociaciones);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
