<?php
// API para obtener un ciclo formativo por su id (Fase 1)
// Equivalente a v3/ajax/ciclos/cargar_ciclo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$idCiclo = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idCiclo <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT id, nombre, familia, nivel FROM ciclos WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $idCiclo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'No encontrado']);
    exit;
}

echo json_encode($row);
mysqli_free_result($res);
mysqli_close($db);
?>
