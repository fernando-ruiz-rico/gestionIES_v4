<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Filtro opcional por curso (fiel a v3: la vista principal de materias se
// filtra por idCurso; sin idCurso se devuelven todas las materias).
$idCurso = isset($_GET['idCurso']) ? intval($_GET['idCurso']) : 0;

if ($idCurso > 0) {
    $stmt = mysqli_prepare($db, "SELECT * FROM materias WHERE idCurso = ? ORDER BY nombre");
    mysqli_stmt_bind_param($stmt, "i", $idCurso);
} else {
    $stmt = mysqli_prepare($db, "SELECT * FROM materias ORDER BY nombre");
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db)]);
    exit;
}

$materias = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $materias[] = $fila;
}
mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($db);
echo json_encode($materias);
?>
