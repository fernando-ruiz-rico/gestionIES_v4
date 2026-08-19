<?php
// API endpoint para cargar todos los departamentos
// Devuelve un array JSON con los departamentos ordenados por nombre

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM departamentos ORDER BY nombre");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$departamentos = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $departamentos[] = $fila;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode($departamentos);
?>
