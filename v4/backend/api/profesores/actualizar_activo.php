<?php
// API endpoint para activar/desactivar un profesor
// Requiere sesión iniciada y rol de admin
// Devuelve: success (true/false), mensaje

require_once '../../config.php';
cabeceraJson();

// Verificar permisos de administrador
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
if (empty($datos['idProfesor'])) {
    sendJSONError('ID de profesor no proporcionado', 400);
}

$idProfesor = datosOptimoInt($datos, 'idProfesor');

try {
    $db = Db::open();

    // Activar/Desactivar profesor (toggle !activo)
    $db->execute("UPDATE profesores SET activo = !activo WHERE id = ?", $idProfesor);
} catch (DbException $e) {
    sendJSONError('Error al actualizar el estado del profesor: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('mensaje' => 'Estado del profesor actualizado correctamente'));
?>
