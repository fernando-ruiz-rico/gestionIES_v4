<?php
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
$id = trim(datosOptimo($datos, 'id'));

if (empty($id)) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $afectadas = $db->execute("DELETE FROM especialidades WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas === 0) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess(null, 'Eliminado correctamente');
?>
