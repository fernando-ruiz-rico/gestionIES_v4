<?php
// API de Configuración: cambia la contraseña del administrador
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();

try {
    $db = Db::open();

    $antiguo = datosOptimo($datos, 'passwordActual');
    $nuevo = datosOptimo($datos, 'nuevaPassword');
    $repetirNuevo = datosOptimo($datos, 'passwordConfirmacion');
    if ($nuevo !== $repetirNuevo) {
        throw new Exception('La nueva contraseña y la repetición no coinciden');
    }

    $afectadas = $db->execute("UPDATE config SET valor=md5(?) WHERE clave='admin' AND valor=md5(?)", $nuevo, $antiguo);
    $db->close();
    if ($afectadas == 0) {
        throw new Exception('La contraseña antigua no es correcta');
    }

    sendJSONSuccess(null, 'Contraseña de administrador actualizada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
