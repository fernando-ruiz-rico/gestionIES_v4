<?php
// API endpoint para asignar/quitar jefe de departamento a un profesor
// Requiere sesión iniciada y rol de admin
// Devuelve: success (true/false), mensaje

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Verificar permisos de administrador
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

if (empty($_POST['idProfesor']) || empty($_POST['idDepartamento'])) {
    sendJSONError('ID de profesor y departamento son requeridos', 400);
}

$idProfesor = intval($_POST['idProfesor']);
$idDepartamento = intval($_POST['idDepartamento']);

try {
    $db = Db::open();

    // Asignar/Quitar jefe de departamento (toggle 1 - jefe_departamento)
    $db->execute("UPDATE profesores SET jefe_departamento = 1 - jefe_departamento WHERE id = ?", $idProfesor);
} catch (DbException $e) {
    sendJSONError('Error al actualizar el jefe de departamento: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('mensaje' => 'Jefe de departamento actualizado correctamente'));
?>
