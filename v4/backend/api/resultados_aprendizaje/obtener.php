<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// devuelve un resultado de aprendizaje concreto
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $id = getOptimoInt('id');
    if ($id <= 0) {
        throw new Exception('ID de resultado inválido');
    }

    $fila = $db->fetchOne("SELECT * FROM resultados_aprendizaje WHERE id = ?", $id);
    $db->close();
    if (!$fila) {
        sendJSONError('Resultado no encontrado', 404);
    }
    sendJSONSuccess($fila);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
