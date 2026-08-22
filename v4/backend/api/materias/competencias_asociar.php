<?php
// Asocia una competencia profesional a una materia.
// Fiel a v3: v3/ajax/materias/nueva_competencia_materia.php (solo admin).
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

// Evita duplicados (PK de competencias_materias es (idMateria, idCompetencia))
$res = mysqli_query($db, "SELECT * FROM competencias_materias WHERE idMateria = " . intval($idMateria) . " AND idCompetencia = " . intval($idCompetencia));
$yaAsociada = mysqli_num_rows($res) > 0;
mysqli_free_result($res);
mysqli_close($db);

if ($yaAsociada) {
    echo json_encode(['success' => true, 'message' => 'La competencia ya está asociada']);
    exit;
}

$db = getDBConnection();
$stmt = mysqli_prepare($db, "INSERT INTO competencias_materias (idCompetencia, idMateria) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "ii", $idCompetencia, $idMateria);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode([
    'success' => (bool) $ok,
    'message' => $ok ? 'Competencia asociada' : 'Error al asociar la competencia'
]);
?>
