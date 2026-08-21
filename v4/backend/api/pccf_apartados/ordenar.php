<?php
// API para reordenar los apartados del PCCF (Fase 3.2 - Apartados PCCF)
// Recibe un parámetro "orden" con los códigos de los apartados en el nuevo orden.
// Cada código puede venir con el prefijo "ap" (p. ej. ap1, ap12) o sin él.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$permisos = ($rol == 'admin' || $rol == 'jefeDepartamento');
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

$orden = isset($_POST['orden']) ? $_POST['orden'] : (isset($_GET['orden']) ? $_GET['orden'] : '');
if ($orden === '') {
    sendJSONError('No se recibió el orden', 400);
}

$partes = explode(',', $orden);

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $i = 0;
    foreach ($partes as $parte) {
        $parte = trim($parte);
        if ($parte === '') continue;
        // Eliminamos el prefijo "ap" si está presente.
        $codApartado = strtolower(substr($parte, 0, 2)) === 'ap' ? substr($parte, 2) : $parte;
        $codApartado = intval($codApartado);
        if ($codApartado <= 0) continue;
        $i++;
        $stmt = mysqli_prepare($db, "UPDATE apartados_pccf SET orden = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $i, $codApartado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    sendJSONSuccess(null, 'Orden actualizado correctamente');
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
