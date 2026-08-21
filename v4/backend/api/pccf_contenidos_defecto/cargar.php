<?php
// API para cargar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Devuelve el texto asociado a un apartado y un departamento concretos.

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

try {
    $stmt = mysqli_prepare($db, "SELECT texto FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?");
    mysqli_stmt_bind_param($stmt, "ii", $idApartado, $idDepartamento);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $texto = '';
    if (mysqli_num_rows($result) > 0) {
        $fila = mysqli_fetch_assoc($result);
        $texto = $fila['texto'];
    }
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    echo json_encode(['success' => true, 'data' => ['texto' => $texto]]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    closeDBConnection($db);
}
?>
