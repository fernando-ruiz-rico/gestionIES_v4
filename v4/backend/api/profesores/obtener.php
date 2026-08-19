<?php
// API endpoint para cargar un profesor específico por ID
// Devuelve: objeto JSON con los datos del profesor

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de profesor no proporcionado']);
    exit;
}

$id = intval($_GET['id']);
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM profesores WHERE id = $id");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$profesor = mysqli_fetch_assoc($result);

if (!$profesor) {
    http_response_code(404);
    echo json_encode(['error' => 'Profesor no encontrado']);
    exit;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode($profesor);
?>
