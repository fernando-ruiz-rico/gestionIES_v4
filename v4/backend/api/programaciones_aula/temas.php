<?php
// API: Listar temas (unidades) de una materia
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

$idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
if ($idMateria <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Debe indicar una materia']);
    exit;
}

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT id, orden, titulo FROM temas WHERE idMateria = ? ORDER BY orden");
mysqli_stmt_bind_param($stmt, "i", $idMateria);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . mysqli_error($db)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$temas = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $temas[] = [
        'id'      => intval($fila['id']),
        'orden'   => intval($fila['orden']),
        'titulo'  => $fila['titulo']
    ];
}

mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $temas]);
?>
