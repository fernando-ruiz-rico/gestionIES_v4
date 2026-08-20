<?php
// API endpoint para eliminar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

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

// Eliminar contenidos relacionados primero
mysqli_query($db, "DELETE FROM contenidos_defecto_programaciones WHERE idApartado = $id");
mysqli_query($db, "DELETE FROM contenidos_programaciones WHERE idApartado = $id");

// Eliminar el apartado
$sql = "DELETE FROM apartados_programaciones WHERE id = $id";
$result = mysqli_query($db, $sql);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . mysqli_error($db)]);
}

mysqli_close($db);
?>
