<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// reordena las competencias de un ciclo
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

    $orden = datosOptimo($datos, 'orden');
    $ids = explode(",", $orden);
    foreach ($ids as $pos => $cod) {
        $idComp = intval(substr($cod, 2));
        if ($idComp > 0) {
            $db->execute("UPDATE competencias_ciclos SET orden=? WHERE id=?", $pos + 1, $idComp);
        }
    }
    sendJSONSuccess(null, 'Orden actualizado');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
