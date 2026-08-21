<?php
// API para listar los cursos (Fase 1)
// Equivalente a v3/ajax/cursos/cargar_cursos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM cursos ORDER BY orden, nombre");
$data = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $data[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);

echo json_encode($data);
?>
