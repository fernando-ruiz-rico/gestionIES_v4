<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$result = mysqli_query($db, "SELECT c.*, e.nombre as especialidad FROM ciclos c LEFT JOIN especialidades e ON c.idEspecialidad = e.idEspecialidad ORDER BY c.nombre");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db)]);
    exit;
}

$ciclos = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $ciclos[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);
echo json_encode($ciclos);
?>
