<?php
// API para eliminar un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Elimina el apartado indicado y sus conexiones en contenidos y contenidos por defecto.

require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$id = getOptimoInt('id');
if ($id <= 0) {
    sendJSONError('Apartado no válido', 400);
}

try {
    $db = Db::open();

    // Eliminamos primero los contenidos relacionados (fidilidad a v3).
    foreach (['contenidos_pccf', 'contenidos_defecto_pccf'] as $tabla) {
        $db->execute("DELETE FROM $tabla WHERE id = ?", $id);
    }

    $db->execute("DELETE FROM apartados_pccf WHERE id = ?", $id);
    sendJSONSuccess(null, 'Apartado eliminado correctamente');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
