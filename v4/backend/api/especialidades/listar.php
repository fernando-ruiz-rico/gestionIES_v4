<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$result = mysqli_query($db, "SELECT e.*, d.nombre as departamento FROM especialidades e LEFT JOIN departamentos d ON e.idDepartamento = d.idDepartamento ORDER BY e.descripcion");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db)]);
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
