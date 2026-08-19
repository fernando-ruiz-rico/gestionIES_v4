<?php
// API endpoint para cargar todas las especialidades
// Devuelve un array JSON con las especialidades ordenadas por nombre

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM especialidades ORDER BY nombre");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$especialidades = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $especialidades[] = $fila;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode($especialidades);
?>
