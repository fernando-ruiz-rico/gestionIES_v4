<?php
// API para listar los apartados del PCCF (Fase 3.2 - Apartados PCCF)
// Devuelve el listado de apartados ordenados por su posición en la tabla.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $result = mysqli_query($db, "SELECT * FROM apartados_pccf ORDER BY orden");
    if (!$result) {
        sendJSONError('Error al listar los apartados: ' . mysqli_error($db));
    }

    $apartados = [];
    while ($fila = mysqli_fetch_assoc($result)) {
        $apartados[] = $fila;
    }
    mysqli_free_result($result);

    sendJSONSuccess($apartados);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
