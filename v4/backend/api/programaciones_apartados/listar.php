<?php
// API endpoint para listar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM apartados_programaciones ORDER BY orden");

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$apartados = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $apartados[] = $fila;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $apartados]);
?>
