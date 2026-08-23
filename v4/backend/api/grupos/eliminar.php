<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
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

    $afectadas = $db->execute("DELETE FROM grupos WHERE id = ?", $id);

    if ($afectadas === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'No encontrado']);
        exit;
    }

    // Fiel a v3: se eliminan también las elecciones y configuraciones de materias
    // que tengan que ver con ese grupo (evita filas huérfanas).
    $db->execute("DELETE FROM materias_grupos WHERE idGrupo = ?", $id);
    $db->execute("DELETE FROM programaciones_aula_temas WHERE idGrupo = ?", $id);
    $db->execute("DELETE FROM seleccion WHERE idGrupo = ?", $id);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);
?>
