<?php
// API endpoint para ordenar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$orden = isset($_POST['orden']) ? $_POST['orden'] : '';

if (empty($orden)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Orden no válido']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$partes = explode(",", $orden);

for ($i = 1; $i <= count($partes); $i++) {
    // Eliminar el prefijo "ap" del apartado actual
    $codApartado = intval(substr($partes[$i-1], 2));
    mysqli_query($db, "UPDATE apartados_programaciones SET orden=$i WHERE id=$codApartado");
}

echo json_encode(['success' => true]);
mysqli_close($db);
?>
