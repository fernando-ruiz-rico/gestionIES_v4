<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// La tabla real para escenarios es 'escenarios_desideratas'
$result = mysqli_query($db, "SELECT id, nombre, actual, activo_desideratas, modo_rueda FROM escenarios_desideratas ORDER BY nombre");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db)]);
    exit;
}

$escenarios = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $escenarios[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);
echo json_encode($escenarios);
?>
