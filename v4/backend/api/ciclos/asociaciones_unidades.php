<?php
// API para listar las unidades de competencia asociadas a un ciclo y las
// disponibles para asociar (Fase 1).
// Equivalente a v3/ajax/ciclos/cargar_asociaciones_unidades.php
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

// Unidades ya asociadas al ciclo
$result = mysqli_query($db, "SELECT uc.codigo, uc.texto
                              FROM unidades_ciclos u
                              INNER JOIN unidades_competencia uc ON uc.codigo = u.codigoUnidad
                              WHERE u.idCiclo = $idCiclo
                              ORDER BY uc.codigo");
$asociadas = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $asociadas[] = $fila;
}
mysqli_free_result($result);

// Unidades que aún no están asociadas a este ciclo
$result = mysqli_query($db, "SELECT uc.codigo, uc.texto
                              FROM unidades_competencia uc
                              WHERE NOT EXISTS (
                                  SELECT 1 FROM unidades_ciclos u
                                  WHERE u.codigoUnidad = uc.codigo AND u.idCiclo = $idCiclo
                              )
                              ORDER BY uc.codigo");
$disponibles = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $disponibles[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['asociadas' => $asociadas, 'disponibles' => $disponibles]);
?>
