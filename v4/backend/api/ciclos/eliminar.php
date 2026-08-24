<?php
// API para eliminar un ciclo formativo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_ciclo.php
// No se puede borrar un ciclo si tiene cursos asociados (tabla cursos_ciclos).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$idCiclo = isset($datos['id']) ? intval($datos['id']) : 0;
if ($idCiclo <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    // Si el ciclo tiene cursos asociados no se puede borrar
    $asociados = $db->fetchOne("SELECT COUNT(*) AS total FROM cursos_ciclos WHERE idCiclo = ?", $idCiclo);

    if ($asociados['total'] > 0) {
        sendJSONError('El ciclo tiene cursos asociados. Elimina primero esas asociaciones.', 409);
    }

    // Borramos las unidades de competencia asociadas al ciclo
    $db->execute("DELETE FROM unidades_ciclos WHERE idCiclo = ?", $idCiclo);
    $afectadas = $db->execute("DELETE FROM ciclos WHERE id = ?", $idCiclo);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas == 0) {
    sendJSONError('No se ha eliminado nada', 404);
}

sendJSONSuccess(null, 'Ciclo eliminado');
?>
