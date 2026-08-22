<?php
// API para crear o modificar un curso (Fase 1)
// Equivalente a v3/ajax/cursos/insertar_curso.php
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
$nombre      = isset($datos['nombre']) ? trim($datos['nombre']) : '';
$abreviatura = isset($datos['abreviatura']) ? trim($datos['abreviatura']) : '';
$horas       = isset($datos['horas_semana']) ? intval($datos['horas_semana']) : 0;
$categoria   = isset($datos['categoria']) ? trim($datos['categoria']) : '';
$id          = isset($datos['id']) ? intval($datos['id']) : 0;

if ($nombre === '' || $abreviatura === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios (nombre y abreviatura)']);
    exit;
}

// En v3 el campo "horas semanales" puede llegar vacío; en ese caso se guarda 0
if ($id > 0) {
    $stmt = mysqli_prepare($db, "UPDATE cursos SET nombre = ?, abreviatura = ?, horas_semana = ?, categoria = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $abreviatura, $horas, $categoria, $id);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO cursos (nombre, abreviatura, horas_semana, categoria) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $abreviatura, $horas, $categoria);
}

$exito = mysqli_stmt_execute($stmt);
$nuevoId = ($id > 0) ? $id : mysqli_insert_id($db);
mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode([
    'success' => (bool) $exito,
    'message' => $exito ? 'Curso guardado correctamente' : 'Error al guardar el curso',
    'id' => (int) $nuevoId
]);
?>
