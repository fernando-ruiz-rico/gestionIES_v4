<?php
// API endpoint para crear o editar especialidades
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre = trim($datos['nombre'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$idEspecialidad = isset($datos['idEspecialidad']) ? intval($datos['idEspecialidad']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre es obligatorio']);
    exit;
}

try {
    if ($idEspecialidad > 0) {
        // Actualizar
        $stmt = mysqli_prepare($db, "UPDATE especialidades SET nombre = ?, descripcion = ? WHERE idEspecialidad = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $nombre, $descripcion, $idEspecialidad);
        $exito = mysqli_stmt_execute($stmt);
        $mensaje = 'Especialidad actualizada correctamente';
    } else {
        // Crear
        $stmt = mysqli_prepare($db, "INSERT INTO especialidades (nombre, descripcion) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $nombre, $descripcion);
        $exito = mysqli_stmt_execute($stmt);
        $mensaje = 'Especialidad creada correctamente';
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    
    if ($exito) {
        echo json_encode(['success' => true, 'message' => $mensaje]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la especialidad']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
