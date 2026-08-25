<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// lista los ciclos disponibles (para el selector de la vista)
// Fiel a v3: las competencias se almacenan en competencias_ciclos.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $sql = "SELECT id, nombre, nivel FROM ciclos ORDER BY nombre";
    $ciclos = $db->fetchAll($sql);
    sendJSONSuccess($ciclos);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
