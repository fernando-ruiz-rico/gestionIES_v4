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
    http_response_code(403);
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

if (empty($_POST['idProfesor']) || empty($_POST['idDepartamento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de profesor y departamento son requeridos']);
    exit;
}

$idProfesor = intval($_POST['idProfesor']);
$idDepartamento = intval($_POST['idDepartamento']);
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $db = new Db($conn);

    // Asignar/Quitar jefe de departamento (toggle 1 - jefe_departamento)
    $db->execute("UPDATE profesores SET jefe_departamento = 1 - jefe_departamento WHERE id = $idProfesor");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el jefe de departamento: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'mensaje' => 'Jefe de departamento actualizado correctamente']);
?>
