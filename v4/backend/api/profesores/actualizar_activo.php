<?php
// API endpoint para activar/desactivar un profesor
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

if (empty($_POST['idProfesor'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de profesor no proporcionado']);
    exit;
}

$idProfesor = intval($_POST['idProfesor']);
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $db = new Db($conn);

    // Activar/Desactivar profesor (toggle !activo)
    $db->execute("UPDATE profesores SET activo = !activo WHERE id = $idProfesor");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el estado del profesor: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'mensaje' => 'Estado del profesor actualizado correctamente']);
?>
