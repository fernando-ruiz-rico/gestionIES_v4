<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) { http_response_code(500); echo json_encode(['error' => 'Error de conexión']); exit; }

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) { http_response_code(400); echo json_encode(['error' => 'Datos inválidos']); exit; }

$nombre = trim($datos['nombre'] ?? '');
$idEspecialidad = isset($datos['idEspecialidad']) ? intval($datos['idEspecialidad']) : 0;
$idCiclo = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;

if (empty($nombre)) { http_response_code(400); echo json_encode(['error' => 'Nombre obligatorio']); exit; }

try {
    if ($idCiclo > 0) {
        $stmt = mysqli_prepare($db, "UPDATE ciclos SET nombre = ?, idEspecialidad = ? WHERE idCiclo = ?");
        mysqli_stmt_bind_param($stmt, "sii", $nombre, $idEspecialidad, $idCiclo);
    } else {
        $stmt = mysqli_prepare($db, "INSERT INTO ciclos (nombre, idEspecialidad) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $nombre, $idEspecialidad);
    }
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    
    echo json_encode(['success' => $exito, 'message' => $exito ? 'Guardado correctamente' : 'Error al guardar']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
