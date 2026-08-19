<?php
// API endpoint para cargar todos los profesores de un departamento
// Recibe: idDepartamento (requerido)
// Devuelve: array JSON con los profesores ordenados por campo 'orden'

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['idDepartamento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$idDepartamento = intval($_GET['idDepartamento']);
$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = $idDepartamento ORDER BY orden");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$profesores = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $profesores[] = $fila;
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode($profesores);
?>
