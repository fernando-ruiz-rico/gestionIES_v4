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
    http_response_code(403);
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

if (empty($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$id = intval($_POST['id']);

try {
    $db = Db::open();

    // Verificar si tiene profesores asignados
    $profesor = $db->fetchOne("SELECT id FROM profesores WHERE idDepartamento = ?", $id);

    if ($profesor) {
        echo json_encode(['success' => false, 'mensaje' => 'No se puede eliminar el departamento porque tiene profesores asociados']);
        exit;
    }

    // Borramos dependencias con otras tablas
    $db->execute("DELETE FROM especialidades WHERE idDepartamento = ?", $id);
    $db->execute("DELETE FROM actas_departamentos WHERE idDepartamento = ?", $id);
    $db->execute("UPDATE materias SET idDepartamento = NULL WHERE idDepartamento = ?", $id);

    // Borramos el departamento
    $afectadas = $db->execute("DELETE FROM departamentos WHERE id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if ($afectadas == 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el departamento']);
    exit;
}

echo json_encode(['success' => true, 'mensaje' => 'Departamento eliminado correctamente']);
?>
