<?php
// API para eliminar un curso (Fase 1)
// Equivalente a v3/ajax/cursos/borrar_curso.php:
// no se puede borrar un curso que tenga grupos o materias;
// al borrarlo se limpian sus datos seleccion, materias y grupos.
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$id = isset($datos['id']) ? intval($datos['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $db = Db::open();

    // Si el curso tiene grupos o materias no se puede borrar
    $relacionado = $db->fetchOne(
        "SELECT id FROM grupos WHERE idCurso = ? UNION SELECT id FROM materias WHERE idCurso = ?",
        $id, $id);

    if ($relacionado) {
        http_response_code(409);
        echo json_encode(['error' => 'El curso tiene grupos o materias. Elimínalas antes de borrar el curso.']);
        exit;
    }

    // Limpiamos los datos asociados al curso
    $db->execute("DELETE FROM seleccion WHERE idMateria IN (SELECT id FROM materias WHERE idCurso = ?)", $id);
    $db->execute("DELETE FROM materias WHERE idCurso = ?", $id);
    $db->execute("DELETE FROM grupos WHERE idCurso = ?", $id);
    $db->execute("DELETE FROM cursos_ciclos WHERE idCurso = ?", $id);
    $afectadas = $db->execute("DELETE FROM cursos WHERE id = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if ($afectadas == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No se ha eliminado nada']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Curso eliminado']);
?>
