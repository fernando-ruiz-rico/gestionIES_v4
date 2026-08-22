<?php
// API endpoint para eliminar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
if (!$permisos) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Eliminar contenidos relacionados primero (mismo orden que v3)
$stmt = mysqli_prepare($db, "DELETE FROM contenidos_defecto_programaciones WHERE idApartado = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($db, "DELETE FROM contenidos_programaciones WHERE idApartado = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Eliminar el apartado
$stmt = mysqli_prepare($db, "DELETE FROM apartados_programaciones WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . mysqli_error($db)]);
}

mysqli_close($db);
?>
