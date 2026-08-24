<?php
// API endpoint para eliminar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$id = getOptimoInt('id');

if ($id <= 0) {
    sendJSONError('ID no válido', 400);
}

try {
    $db = Db::open();

    //Eliminar contenidos relacionados primero (mismo orden que v3)
    $db->execute("DELETE FROM contenidos_defecto_programaciones WHERE idApartado = ?", $id);
    $db->execute("DELETE FROM contenidos_programaciones WHERE idApartado = ?", $id);

    // Eliminar el apartado
    $db->execute("DELETE FROM apartados_programaciones WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null);
?>
