<?php
// API endpoint para ordenar apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
if (!$permisos) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

$orden = isset($_POST['orden']) ? $_POST['orden'] : '';

if (empty($orden)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Orden no válido']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$partes = explode(",", $orden);
$stmt = mysqli_prepare($db, "UPDATE apartados_programaciones SET orden=? WHERE id=?");

for ($i = 1; $i <= count($partes); $i++) {
    // Eliminar el prefijo "ap" del apartado actual
    $codApartado = intval(substr($partes[$i-1], 2));
    $posicion = $i;
    mysqli_stmt_bind_param($stmt, "ii", $posicion, $codApartado);
    mysqli_stmt_execute($stmt);
}

mysqli_stmt_close($stmt);
echo json_encode(['success' => true]);
mysqli_close($db);
?>
