<?php
// API para crear o modificar un ciclo formativo (Fase 1)
// Equivalente a v3/ajax/ciclos/insertar_ciclo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre   = isset($datos['nombre']) ? trim($datos['nombre']) : '';
$familia  = isset($datos['familia']) ? trim($datos['familia']) : '';
$nivel    = isset($datos['nivel']) ? trim($datos['nivel']) : '';
$idCiclo  = isset($datos['id']) ? intval($datos['id']) : 0;

if ($nombre === '' || $familia === '' || $nivel === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios (nombre, familia y nivel)']);
    exit;
}

if ($idCiclo > 0) {
    $stmt = mysqli_prepare($db, "UPDATE ciclos SET nombre = ?, familia = ?, nivel = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $nombre, $familia, $nivel, $idCiclo);
} else {
    // La columna "horas" es NOT NULL sin valor por defecto en la tabla;
    // v3 no la pide en el formulario, así que se guarda 0
    $stmt = mysqli_prepare($db, "INSERT INTO ciclos (nombre, familia, nivel, horas) VALUES (?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "sss", $nombre, $familia, $nivel);
}

$exito = mysqli_stmt_execute($stmt);
$nuevoId = ($idCiclo > 0) ? $idCiclo : mysqli_insert_id($db);
mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode([
    'success' => (bool) $exito,
    'message' => $exito ? 'Ciclo guardado correctamente' : 'Error al guardar el ciclo',
    'id' => (int) $nuevoId
]);
?>
