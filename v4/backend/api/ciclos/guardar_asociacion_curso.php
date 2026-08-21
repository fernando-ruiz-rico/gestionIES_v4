<?php
// API para asociar un curso a un ciclo o cambiar el orden de la asociación
// (Fase 1). Equivalente a v3/ajax/ciclos/insertar_curso_ciclo.php y
// v3/ajax/ciclos/actualizar_curso_ciclo.php unidos.
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

@session_start();
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo el administrador puede gestionar las asociaciones']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$idCiclo = isset($datos['idCiclo']) ? intval($datos['idCiclo']) : 0;
$idCurso = isset($datos['idCurso']) ? intval($datos['idCurso']) : 0;
$orden   = isset($datos['orden']) ? intval($datos['orden']) : 0;
if ($idCiclo <= 0 || $idCurso <= 0 || $orden <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros no válidos']);
    exit;
}

// Si la asociación ya existe actualizamos el orden; si no, la creamos
$existe = mysqli_query($db, "SELECT COUNT(*) AS total FROM cursos_ciclos WHERE idCiclo = $idCiclo AND idCurso = $idCurso");
$fila = mysqli_fetch_assoc($existe);
mysqli_free_result($existe);
if ($fila['total'] > 0) {
    $ok = mysqli_query($db, "UPDATE cursos_ciclos SET orden = $orden WHERE idCiclo = $idCiclo AND idCurso = $idCurso");
} else {
    $ok = mysqli_query($db, "INSERT INTO cursos_ciclos (idCurso, idCiclo, orden) VALUES ($idCurso, $idCiclo, $orden)");
}

mysqli_close($db);
if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Asociación guardada']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la asociación']);
}
?>
