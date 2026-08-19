<?php
// API endpoint para borrar un profesor por ID
// Requiere sesión iniciada y rol de admin
// Elimina también todos los vínculos que tuviera (selección de materias, preferencias de horario...)
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
    echo json_encode(['error' => 'ID de profesor no proporcionado']);
    exit;
}

$id = intval($_POST['id']);
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Borramos dependencias con otras tablas
mysqli_query($db, "DELETE FROM seleccion WHERE idProfesor = $id");
mysqli_query($db, "DELETE FROM preferencias_horario WHERE idProfesor = $id");
mysqli_query($db, "DELETE FROM programaciones_aula_temas WHERE idProfesor = $id");

// Borramos el profesor
$query = "DELETE FROM profesores WHERE id = $id";
$result = mysqli_query($db, $query);

if (!$result || mysqli_affected_rows($db) == 0) {
    mysqli_close($db);
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el profesor']);
    exit;
}

mysqli_close($db);
echo json_encode(['success' => true, 'mensaje' => 'Profesor eliminado correctamente']);
?>
