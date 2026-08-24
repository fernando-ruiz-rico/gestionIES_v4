<?php
// API para eliminar un escenario (tabla real: escenarios_desideratas)
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
$id = datosOptimoInt($datos, 'id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $afectadas = $db->execute("DELETE FROM escenarios_desideratas WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas === 0) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess(null, 'Escenario eliminado');
?>
