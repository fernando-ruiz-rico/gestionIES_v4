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
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Activar/Desactivar profesor (toggle !activo)
$query = "UPDATE profesores SET activo = !activo WHERE id = $idProfesor";
$result = mysqli_query($db, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el estado del profesor: ' . mysqli_error($db)]);
    exit;
}

mysqli_close($db);
echo json_encode(['success' => true, 'mensaje' => 'Estado del profesor actualizado correctamente']);
?>
