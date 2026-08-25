<?php
// API para la gestión de Actas de departamentos (Fase 6.1):
// devuelve el texto y fecha de un acta
// Fiel a v3: las actas se almacenan en actas_departamentos, una fila por acta.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $idActa = getOptimoInt('idActa');
    if ($idActa <= 0) {
        throw new Exception('ID de acta inválido');
    }

    $fila = $db->fetchOne("SELECT texto, fecha FROM actas_departamentos WHERE id=?", $idActa);
    if (!$fila) {
        sendJSONError('Acta no encontrada', 404);
    }
    sendJSONSuccess($fila);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
