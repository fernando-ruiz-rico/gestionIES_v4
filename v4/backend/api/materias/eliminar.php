<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
$id = intval(isset($datos['id']) ? $datos['id'] : 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Compruebo que existe antes de borrar (fiel a v3)
$stmt = mysqli_prepare($db, "SELECT id FROM materias WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) === 0) {
    mysqli_stmt_close($stmt);
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    mysqli_close($db);
    exit;
}
mysqli_stmt_close($stmt);

// Borrado en cascada (fiel a v3/borrar_materia.php): las tablas que la huérfanan
// antes que la propia materia, para no dejar filas huérfanas (ver B-6).
mysqli_query($db, "DELETE FROM seleccion WHERE idMateria = " . intval($id));
mysqli_query($db, "DELETE FROM materias_grupos WHERE idMateria = " . intval($id));
mysqli_query($db, "DELETE FROM contenidos_programaciones WHERE idMateria = " . intval($id));

$stmt = mysqli_prepare($db, "DELETE FROM materias WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
