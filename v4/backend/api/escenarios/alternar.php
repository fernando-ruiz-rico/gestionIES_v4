<?php
// API para cambiar el estado de un escenario (fiel a v3):
//  - actual: "escenario en vigor" (v3/actualizar_escenario_actual.php)
//  - activo_desideratas: "elegible en desideratas" (v3/actualizar_escenario_activo_desideratas.php)
//  - modo_rueda: escenario en "modo rueda" (v3/actualizar_modo_rueda.php)
// En los tres casos v3 simplemente invertía el valor guardado, y aquí se hace igual
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
$id = datosOptimoInt($datos, 'id');
$campo = datosOptimo($datos, 'campo');

// Solo se admiten los tres campos conmutables
if (!in_array($campo, array('actual', 'activo_desideratas', 'modo_rueda'))) {
    sendJSONError('Campo no válido', 400);
}
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

// Inversión fiel a v3: si estaba a 1 pasa a 0 y a la inversa
try {
    $db = Db::open();
    $afectadas = $db->execute("UPDATE escenarios_desideratas SET $campo = 1 - $campo WHERE id = ?", $id);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas === 0) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess(null, 'Estado actualizado');
?>
