<?php
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
$nombre = trim(datosOptimo($datos, 'nombre'));
$id = datosOptimoInt($datos, 'id');
$actual = datosOptimoInt($datos, 'actual');
$activo_desideratas = datosOptimoInt($datos, 'activo_desideratas');
$modo_rueda = datosOptimoInt($datos, 'modo_rueda');

if (empty($nombre)) {
    sendJSONError('Nombre obligatorio', 400);
}

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    if ($id > 0) {
        $db->execute("UPDATE escenarios_desideratas SET nombre=?, actual=?, activo_desideratas=?, modo_rueda=? WHERE id=?", $nombre, $actual, $activo_desideratas, $modo_rueda, $id);
    } else {
        $db->execute("INSERT INTO escenarios_desideratas (nombre, actual, activo_desideratas, modo_rueda) VALUES (?, ?, ?, ?)", $nombre, $actual, $activo_desideratas, $modo_rueda);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $id), 'Guardado');
?>
