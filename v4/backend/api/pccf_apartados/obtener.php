<?php
// API para obtener los datos de un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Devuelve en formato JSON los datos del apartado solicitado.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idApartado = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idApartado <= 0) {
    sendJSONError('Apartado no válido', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $stmt = mysqli_prepare($db, "SELECT * FROM apartados_pccf WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idApartado);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $apartado = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        sendJSONSuccess($apartado);
    } else {
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        sendJSONError('Apartado no encontrado', 404);
    }
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
