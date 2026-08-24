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
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    // Si el curso tiene grupos o materias no se puede borrar
    $relacionado = $db->fetchOne(
        "SELECT id FROM grupos WHERE idCurso = ? UNION SELECT id FROM materias WHERE idCurso = ?",
        $id, $id);

    if ($relacionado) {
        sendJSONError('El curso tiene grupos o materias. Elimínalas antes de borrar el curso.', 409);
    }

    // Limpiamos los datos asociados al curso
    $db->execute("DELETE FROM seleccion WHERE idMateria IN (SELECT id FROM materias WHERE idCurso = ?)", $id);
    $db->execute("DELETE FROM materias WHERE idCurso = ?", $id);
    $db->execute("DELETE FROM grupos WHERE idCurso = ?", $id);
    $db->execute("DELETE FROM cursos_ciclos WHERE idCurso = ?", $id);
    $afectadas = $db->execute("DELETE FROM cursos WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas == 0) {
    sendJSONError('No se ha eliminado nada', 404);
}

sendJSONSuccess(null, 'Curso eliminado');
?>
