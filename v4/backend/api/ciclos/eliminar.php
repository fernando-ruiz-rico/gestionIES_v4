<?php
// API para eliminar un ciclo formativo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_ciclo.php
// No se puede borrar un ciclo si tiene cursos asociados (tabla cursos_ciclos).
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
$idCiclo = isset($datos['id']) ? intval($datos['id']) : 0;
if ($idCiclo <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Si el ciclo tiene cursos asociados no se puede borrar
$result = mysqli_query($db, "SELECT COUNT(*) AS total FROM cursos_ciclos WHERE idCiclo = $idCiclo");
$fila = mysqli_fetch_assoc($result);
mysqli_free_result($result);
if ($fila['total'] > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'El ciclo tiene cursos asociados. Elimina primero esas asociaciones.']);
    exit;
}

// Borramos las unidades de competencia asociadas al ciclo
mysqli_query($db, "DELETE FROM unidades_ciclos WHERE idCiclo = $idCiclo");
mysqli_query($db, "DELETE FROM ciclos WHERE id = $idCiclo");

if (mysqli_affected_rows($db) == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No se ha eliminado nada']);
    exit;
}

closeDBConnection($db);
echo json_encode(['success' => true, 'message' => 'Ciclo eliminado']);
?>
