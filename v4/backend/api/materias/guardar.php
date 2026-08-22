<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$idCurso = intval(isset($datos['idCurso']) ? $datos['idCurso'] : 0);
$idDepartamento = intval(isset($datos['idDepartamento']) ? $datos['idDepartamento'] : 0);
$horas = intval(isset($datos['horas']) ? $datos['horas'] : 0);
$tipo = trim(isset($datos['tipo']) ? $datos['tipo'] : 'OTRA');
$id = isset($datos['id']) ? intval($datos['id']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

if ($id > 0) {
    $stmt = mysqli_prepare($db, "UPDATE materias SET nombre=?, idCurso=?, idDepartamento=?, horas=?, tipo=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiisi", $nombre, $idCurso, $idDepartamento, $horas, $tipo, $id);
} else {
    // La columna "grupo" es NOT NULL sin valor por defecto; v3 no la pide, así que se guarda vacía
    $stmt = mysqli_prepare($db, "INSERT INTO materias (nombre, idCurso, idDepartamento, horas, tipo, grupo) VALUES (?, ?, ?, ?, ?, '')");
    mysqli_stmt_bind_param($stmt, "siiss", $nombre, $idCurso, $idDepartamento, $horas, $tipo);
}

$ok = mysqli_stmt_execute($stmt);
$nuevoId = ($id > 0) ? $id : mysqli_insert_id($db);

echo json_encode([
    'success' => (bool) $ok,
    'message' => $ok ? 'Materia guardada correctamente' : 'Error al guardar la materia',
    'id' => (int) $nuevoId
]);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
