<?php
// API endpoint para borrar un profesor por ID
// Requiere sesión iniciada y rol de admin
// Elimina también todos los vínculos que tuviera (selección de materias, preferencias de horario...)
// Devuelve: success (true/false), mensaje

require_once '../../config.php';
cabeceraJson();
session_start();

// Verificar permisos de administrador
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

if (empty($_POST['id'])) {
    sendJSONError('ID de profesor no proporcionado', 400);
}

$id = intval($_POST['id']);

try {
    $db = Db::open();

    // Borramos dependencias con otras tablas
    $db->execute("DELETE FROM seleccion WHERE idProfesor = ?", $id);
    $db->execute("DELETE FROM preferencias_horario WHERE idProfesor = ?", $id);
    $db->execute("DELETE FROM programaciones_aula_temas WHERE idProfesor = ?", $id);

    // Borramos el profesor
    $afectadas = $db->execute("DELETE FROM profesores WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error al eliminar el profesor: ' . $e->getMessage(), 500);
}

if ($afectadas == 0) {
    sendJSONError('Error al eliminar el profesor', 404);
}

sendJSONSuccess(array('mensaje' => 'Profesor eliminado correctamente'));
?>
