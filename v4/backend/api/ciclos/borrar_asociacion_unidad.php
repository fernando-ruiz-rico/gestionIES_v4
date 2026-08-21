<?php
// API para desasociar una unidad de competencia de un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_asociacion.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

@session_start();
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo el administrador puede gestionar las asociaciones']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$idCiclo      = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;
$CodigoUnidad = isset($datos['codigoUnidad']) ? trim($datos['codigoUnidad']) : '';
if ($idCiclo <= 0 || $CodigoUnidad === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros no válidos']);
    exit;
}

$CodigoUnidad = mysqli_real_escape_string($db, $CodigoUnidad);
$ok = mysqli_query($db, "DELETE FROM unidades_ciclos WHERE idCiclo = $idCiclo AND codigoUnidad = '$CodigoUnidad'");
$afectadas = mysqli_affected_rows($db);
mysqli_close($db);

if ($ok && $afectadas > 0) {
    echo json_encode(['success' => true, 'message' => 'Asociación eliminada']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'La asociación no existe']);
}
?>
