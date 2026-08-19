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
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre = trim($datos['nombre'] ?? '');
$abreviatura = trim($datos['abreviatura'] ?? '');
$idCurso = intval($datos['idCurso'] ?? 0);
$orden = intval($datos['orden'] ?? 0);
$mostrar = intval($datos['mostrar'] ?? 1);
$horas_complementarias_dual = intval($datos['horas_complementarias_dual'] ?? 0);
$id = isset($datos['id']) ? intval($datos['id']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

if ($id > 0) {
    $stmt = mysqli_prepare($db, "UPDATE grupos SET nombre=?, abreviatura=?, idCurso=?, orden=?, mostrar=?, horas_complementarias_dual=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiiiii", $nombre, $abreviatura, $idCurso, $orden, $mostrar, $horas_complementarias_dual, $id);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO grupos (nombre, abreviatura, idCurso, orden, mostrar, horas_complementarias_dual) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siiii", $nombre, $abreviatura, $idCurso, $orden, $mostrar, $horas_complementarias_dual);
}

$ok = mysqli_stmt_execute($stmt);
if ($ok && $id === 0) {
    $id = mysqli_insert_id($db);
}

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Guardado correctamente' : 'Error al guardar',
    'id' => $ok && $id === 0 ? $id : null
]);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
