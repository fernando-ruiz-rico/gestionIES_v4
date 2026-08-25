<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// elimina una competencia
// Fiel a v3: las competencias se almacenan en competencias_ciclos.
// Permisos: solo el rol admin.
require_once '../../config.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    // Permiso: solo admin
    checkPermission(array(ROLE_ADMIN));

    $id = intval($datos['id']);
    if ($id <= 0) {
        throw new Exception('ID de competencia inválido');
    }

    $db->execute("DELETE FROM competencias_ciclos WHERE id=?", $id);
    sendJSONSuccess(null, 'Competencia eliminada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
