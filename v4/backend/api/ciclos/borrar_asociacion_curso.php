<?php
// API para desasociar un curso de un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_curso_ciclo.php
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
$idCiclo = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;
$idCurso = isset($datos['idCurso']) ? intval($datos['idCurso']) : 0;
if ($idCiclo <= 0 || $idCurso <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros no válidos']);
    exit;
}

$ok = mysqli_query($db, "DELETE FROM cursos_ciclos WHERE idCiclo = $idCiclo AND idCurso = $idCurso");
$afectadas = mysqli_affected_rows($db);
mysqli_close($db);

if ($ok && $afectadas > 0) {
    echo json_encode(['success' => true, 'message' => 'Asociación eliminada']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'La asociación no existe']);
}
?>
