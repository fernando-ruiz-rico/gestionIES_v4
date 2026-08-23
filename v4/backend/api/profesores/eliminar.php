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
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $db = new Db($conn);

    // Borramos dependencias con otras tablas
    $db->execute("DELETE FROM seleccion WHERE idProfesor = $id");
    $db->execute("DELETE FROM preferencias_horario WHERE idProfesor = $id");
    $db->execute("DELETE FROM programaciones_aula_temas WHERE idProfesor = $id");

    // Borramos el profesor
    $afectadas = $db->execute("DELETE FROM profesores WHERE id = $id");
} catch (DbException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el profesor']);
    exit;
}

if ($afectadas == 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el profesor']);
    exit;
}

echo json_encode(['success' => true, 'mensaje' => 'Profesor eliminado correctamente']);
?>
