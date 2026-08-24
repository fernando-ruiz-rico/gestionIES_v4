<?php
// API endpoint para ordenar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

// Campo único del endpoint: postOptimo devuelve el valor si llega no vacío, y null si no
$orden = postOptimo('orden');

if (empty($orden)) {
    sendJSONError('Orden no válido', 400);
}

try {
    $db = Db::open();

    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++) {
        //Eliminar el prefijo "ap" del apartado actual
        $codApartado = intval(substr($partes[$i-1], 2));
        $posicion = $i;
        $db->execute("UPDATE apartados_programaciones SET orden=? WHERE id=?", $posicion, $codApartado);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null);
?>
