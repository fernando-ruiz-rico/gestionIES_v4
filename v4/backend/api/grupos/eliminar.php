<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$id = isset($datos['id']) ? intval($datos['id']) : 0;

if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    $afectadas = $db->execute("DELETE FROM grupos WHERE id = ?", $id);

    if ($afectadas === 0) {
        sendJSONError('No encontrado', 404);
    }

    // Fiel a v3: se eliminan también las elecciones y configuraciones de materias
    // que tengan que ver con ese grupo (evita filas huérfanas).
    $db->execute("DELETE FROM materias_grupos WHERE idGrupo = ?", $id);
    $db->execute("DELETE FROM programaciones_aula_temas WHERE idGrupo = ?", $id);
    $db->execute("DELETE FROM seleccion WHERE idGrupo = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null, 'Eliminado correctamente');
?>
