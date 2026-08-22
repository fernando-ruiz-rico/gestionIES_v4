<?php
// API para eliminar un curso (Fase 1)
// Equivalente a v3/ajax/cursos/borrar_curso.php:
// no se puede borrar un curso que tenga grupos o materias;
// al borrarlo se limpian sus datos seleccion, materias y grupos.
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
$id = isset($datos['id']) ? intval($datos['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Si el curso tiene grupos o materias no se puede borrar
$result = mysqli_query($db, "SELECT id FROM grupos WHERE idCurso = $id UNION SELECT id FROM materias WHERE idCurso = $id");
if (mysqli_num_rows($result) > 0) {
    mysqli_free_result($result);
    http_response_code(409);
    echo json_encode(['error' => 'El curso tiene grupos o materias. Elimínalas antes de borrar el curso.']);
    exit;
}
mysqli_free_result($result);

// Limpiamos los datos asociados al curso
mysqli_query($db, "DELETE FROM seleccion WHERE idMateria IN (SELECT id FROM materias WHERE idCurso = $id)");
mysqli_query($db, "DELETE FROM materias WHERE idCurso = $id");
mysqli_query($db, "DELETE FROM grupos WHERE idCurso = $id");
mysqli_query($db, "DELETE FROM cursos_ciclos WHERE idCurso = $id");
mysqli_query($db, "DELETE FROM cursos WHERE id = $id");

if (mysqli_affected_rows($db) == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No se ha eliminado nada']);
    exit;
}

mysqli_close($db);
echo json_encode(['success' => true, 'message' => 'Curso eliminado']);
?>
