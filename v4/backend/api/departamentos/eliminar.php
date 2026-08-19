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
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar si tiene profesores asignados
$result = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = $id");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar profesores: ' . mysqli_error($db)]);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    mysqli_free_result($result);
    mysqli_close($db);
    echo json_encode(['success' => false, 'mensaje' => 'No se puede eliminar el departamento porque tiene profesores asociados']);
    exit;
}

mysqli_free_result($result);

// Borramos dependencias con otras tablas
mysqli_query($db, "DELETE FROM especialidades WHERE idDepartamento = $id");
mysqli_query($db, "DELETE FROM actas_departamentos WHERE idDepartamento = $id");
mysqli_query($db, "UPDATE materias SET idDepartamento = NULL WHERE idDepartamento = $id");

// Borramos el departamento
$query = "DELETE FROM departamentos WHERE id = $id";
$result = mysqli_query($db, $query);

if (!$result || mysqli_affected_rows($db) == 0) {
    mysqli_close($db);
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el departamento']);
    exit;
}

mysqli_close($db);
echo json_encode(['success' => true, 'mensaje' => 'Departamento eliminado correctamente']);
?>
