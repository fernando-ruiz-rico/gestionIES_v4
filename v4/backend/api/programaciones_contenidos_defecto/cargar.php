<?php
// API endpoint para cargar contenido por defecto de un apartado
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idApartado = isset($_GET['idApartado']) ? intval($_GET['idApartado']) : 0;
$idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;

if ($idApartado <= 0 || $idDepartamento <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros no válidos']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT texto FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?");
mysqli_stmt_bind_param($stmt, "ii", $idApartado, $idDepartamento);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'data' => ['texto' => $fila['texto']]]);
} else {
    echo json_encode(['success' => true, 'data' => ['texto' => '']]);
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
