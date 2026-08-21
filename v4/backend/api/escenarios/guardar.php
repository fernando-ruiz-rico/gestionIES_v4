<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$id = isset($datos['id']) ? intval($datos['id']) : 0;
$actual = isset($datos['actual']) ? intval($datos['actual']) : 0;
$activo_desideratas = isset($datos['activo_desideratas']) ? intval($datos['activo_desideratas']) : 0;
$modo_rueda = isset($datos['modo_rueda']) ? intval($datos['modo_rueda']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

// La tabla real para escenarios es 'escenarios_desideratas'
if ($id > 0) {
    $stmt = mysqli_prepare($db, "UPDATE escenarios_desideratas SET nombre=?, actual=?, activo_desideratas=?, modo_rueda=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiii", $nombre, $actual, $activo_desideratas, $modo_rueda, $id);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO escenarios_desideratas (nombre, actual, activo_desideratas, modo_rueda) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siii", $nombre, $actual, $activo_desideratas, $modo_rueda);
}

$ok = mysqli_stmt_execute($stmt);
echo json_encode(['success' => $ok, 'message' => $ok ? 'Guardado' : 'Error']);
mysqli_close($db);
?>
