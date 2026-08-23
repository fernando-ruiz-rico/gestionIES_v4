<?php
// Quita la asociación de una competencia a una materia.
// Fiel a v3: v3/ajax/materias/borrar_competencia_materia.php (solo admin).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$idMateria = intval(isset($datos['idMateria']) ? $datos['idMateria'] : 0);
$idCompetencia = intval(isset($datos['idCompetencia']) ? $datos['idCompetencia'] : 0);

if ($idMateria <= 0 || $idCompetencia <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

try {
    $db = new Db($conn);

    $afectadas = $db->execute("DELETE FROM competencias_materias WHERE idMateria = ? AND idCompetencia = ?", $idMateria, $idCompetencia);
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al borrar la competencia: ' . $e->getMessage()]);
    exit;
}

if ($afectadas === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Competencia desvinculada']);
?>
