<?php
// API: Listar las evaluaciones disponibles (seguimiento de programaciones)
// Equivalente a v3 includes/cargar_evaluaciones.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$result = mysqli_query($db, "SELECT id, nombre FROM evaluaciones ORDER BY id");

if (!$result) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$evaluaciones = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $evaluaciones[] = [
        'id'      => intval($fila['id']),
        'nombre'  => $fila['nombre']
    ];
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $evaluaciones]);
?>
