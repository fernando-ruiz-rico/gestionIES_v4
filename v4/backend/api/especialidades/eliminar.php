<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$id = trim(isset($datos['id']) ? $datos['id'] : '');

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
