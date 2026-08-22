<?php
// API para asociar una unidad de competencia a un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/nueva_asociacion.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$idCiclo      = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;
$CodigoUnidad = isset($datos['codigoUnidad']) ? trim($datos['codigoUnidad']) : '';
if ($idCiclo <= 0 || $CodigoUnidad === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros no válidos']);
    exit;
}

$CodigoUnidad = mysqli_real_escape_string($db, $CodigoUnidad);
$ok = mysqli_query($db, "INSERT IGNORE INTO unidades_ciclos (idCiclo, codigoUnidad) VALUES ($idCiclo, '$CodigoUnidad')");
mysqli_close($db);

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Unidad asociada al ciclo']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al asociar la unidad']);
}
?>
