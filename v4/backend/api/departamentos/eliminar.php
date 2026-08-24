<?php
// API endpoint para borrar un departamento por ID
// Requiere sesión iniciada y rol de admin
// Solo borra si no tiene profesores asociados
// Devuelve: success (true/false), mensaje

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Verificar permisos de administrador
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

if (empty($_POST['id'])) {
    sendJSONError('ID de departamento no proporcionado', 400);
}

$id = intval($_POST['id']);

try {
    $db = Db::open();

    // Verificar si tiene profesores asignados
    $profesor = $db->fetchOne("SELECT id FROM profesores WHERE idDepartamento = ?", $id);

    if ($profesor) {
        sendJSONError('No se puede eliminar el departamento porque tiene profesores asociados', 200);
    }

    // Borramos dependencias con otras tablas
    $db->execute("DELETE FROM especialidades WHERE idDepartamento = ?", $id);
    $db->execute("DELETE FROM actas_departamentos WHERE idDepartamento = ?", $id);
    $db->execute("UPDATE materias SET idDepartamento = NULL WHERE idDepartamento = ?", $id);

    // Borramos el departamento
    $afectadas = $db->execute("DELETE FROM departamentos WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas == 0) {
    sendJSONError('Error al eliminar el departamento', 200);
}

sendJSONSuccess(null, 'Departamento eliminado correctamente');
?>
