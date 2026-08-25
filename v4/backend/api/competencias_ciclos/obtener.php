<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// devuelve una competencia concreta
// Fiel a v3: las competencias se almacenan en competencias_ciclos, una fila
// por competencia (con su código, texto, tipo e id de ciclo).
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $id = getOptimoInt('id');
    if ($id <= 0) {
        throw new Exception('ID de competencia inválido');
    }

    $fila = $db->fetchOne("SELECT * FROM competencias_ciclos WHERE id=?", $id);
    if (!$fila) {
        sendJSONError('Competencia no encontrada', 404);
    }
    sendJSONSuccess($fila);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
