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

$id = trim($datos['id'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$idDepartamento = intval($datos['idDepartamento'] ?? 0);
$horasTutoria = intval($datos['horasTutoria'] ?? 0);
$horasIngles = intval($datos['horasIngles'] ?? 0);

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID obligatorio']);
    exit;
}

if (empty($descripcion)) {
    http_response_code(400);
    echo json_encode(['error' => 'Descripción obligatoria']);
    exit;
}

// Verificar si existe para actualizar o insertar
$stmt = mysqli_prepare($db, "SELECT id FROM especialidades WHERE id = ?");
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$existe = mysqli_fetch_assoc($res);
mysqli_free_result($res);
mysqli_stmt_close($stmt);

if ($existe) {
    $stmt = mysqli_prepare($db, "UPDATE especialidades SET descripcion=?, idDepartamento=?, horasTutoria=?, horasIngles=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiss", $descripcion, $idDepartamento, $horasTutoria, $horasIngles, $id);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO especialidades (id, descripcion, idDepartamento, horasTutoria, horasIngles) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiis", $id, $descripcion, $idDepartamento, $horasTutoria, $horasIngles);
}

$ok = mysqli_stmt_execute($stmt);

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Guardado correctamente' : 'Error al guardar'
]);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
