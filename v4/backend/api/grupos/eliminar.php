<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$id = intval(isset($datos['id']) ? $datos['id'] : 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$stmt = mysqli_prepare($db, "DELETE FROM grupos WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    exit;
}
$afectados = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($afectados === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

// Fiel a v3: se eliminan también las elecciones y configuraciones de materias
// que tengan que ver con ese grupo (evita filas huérfanas).
mysqli_query($db, "DELETE FROM materias_grupos WHERE idGrupo = " . $id);
mysqli_query($db, "DELETE FROM programaciones_aula_temas WHERE idGrupo = " . $id);
mysqli_query($db, "DELETE FROM seleccion WHERE idGrupo = " . $id);

echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);

mysqli_close($db);
?>
