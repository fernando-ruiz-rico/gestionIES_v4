<?php
// API endpoint para cargar un departamento específico por ID
// Devuelve un objeto JSON con los datos del departamento

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$id = intval($_GET['id']);
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM departamentos WHERE id = $id");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$departamento = mysqli_fetch_assoc($result);

if (!$departamento) {
    http_response_code(404);
    echo json_encode(['error' => 'Departamento no encontrado']);
    exit;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode($departamento);
?>
