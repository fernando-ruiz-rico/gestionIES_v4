<?php
// API para listar los ciclos formativos disponibles (Fase 3.1 - PCCF)
// Devuelve el listado de ciclos para el selector del PCCF

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $result = mysqli_query($db, "SELECT * FROM ciclos ORDER BY nombre");
    if (!$result) {
        sendJSONError('Error al listar los ciclos: ' . mysqli_error($db));
    }

    $ciclos = [];
    while ($fila = mysqli_fetch_assoc($result)) {
        $ciclos[] = $fila;
    }
    mysqli_free_result($result);

    sendJSONSuccess($ciclos);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
