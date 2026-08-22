<?php
// API para eliminar un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Elimina el apartado indicado y sus conexiones en contenidos y contenidos por defecto.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendJSONError('Apartado no válido', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    // Eliminamos primero los contenidos relacionados (fidilidad a v3).
    foreach (['contenidos_pccf', 'contenidos_defecto_pccf'] as $tabla) {
        $stmt = mysqli_prepare($db, "DELETE FROM $tabla WHERE idApartado = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $stmt = mysqli_prepare($db, "DELETE FROM apartados_pccf WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        sendJSONError('Error al eliminar el apartado: ' . mysqli_error($db));
    }
    mysqli_stmt_close($stmt);
    sendJSONSuccess(null, 'Apartado eliminado correctamente');
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
