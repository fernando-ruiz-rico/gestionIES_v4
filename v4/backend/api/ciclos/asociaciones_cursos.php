<?php
// API para listar los cursos asociados a un ciclo y los cursos disponibles
// para asociar (Fase 1). Equivalente a v3/ajax/ciclos/cargar_asociaciones_cursos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$idCiclo = isset($_GET['idCiclo']) ? intval($_GET['idCiclo']) : 0;
if ($idCiclo <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Cursos ya asociados al ciclo
$result = mysqli_query($db, "SELECT cc.idCurso, cc.orden, cu.nombre, cu.abreviatura
                              FROM cursos_ciclos cc
                              INNER JOIN cursos cu ON cu.id = cc.idCurso
                              WHERE cc.idCiclo = $idCiclo
                              ORDER BY cc.orden");
$asociados = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $asociados[] = $fila;
}
mysqli_free_result($result);

// Cursos del centro que aún no están asociados a este ciclo
$result = mysqli_query($db, "SELECT cu.id, cu.nombre, cu.abreviatura
                              FROM cursos cu
                              WHERE NOT EXISTS (
                                  SELECT 1 FROM cursos_ciclos cc
                                  WHERE cc.idCurso = cu.id AND cc.idCiclo = $idCiclo
                              )
                              ORDER BY cu.nombre");
$disponibles = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $disponibles[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['asociados' => $asociados, 'disponibles' => $disponibles]);
?>
