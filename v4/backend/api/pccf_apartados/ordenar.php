<?php
// API para reordenar los apartados del PCCF (Fase 3.2 - Apartados PCCF)
// Recibe un parámetro "orden" con los códigos de los apartados en el nuevo orden.
// Cada código puede venir con el prefijo "ap" (p. ej. ap1, ap12) o sin él.

require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$orden = isset($_POST['orden']) ? $_POST['orden'] : (isset($_GET['orden']) ? $_GET['orden'] : '');
if ($orden === '') {
    sendJSONError('No se recibió el orden', 400);
}

$partes = explode(',', $orden);

try {
    $db = Db::open();

    $i = 0;
    foreach ($partes as $parte) {
        $parte = trim($parte);
        if ($parte === '') continue;
        // Eliminamos el prefijo "ap" si está presente.
        $codApartado = strtolower(substr($parte, 0, 2)) === 'ap' ? substr($parte, 2) : $parte;
        $codApartado = intval($codApartado);
        if ($codApartado <= 0) continue;
        $i++;
        $db->execute("UPDATE apartados_pccf SET orden = ? WHERE id = ?", $i, $codApartado);
    }

    sendJSONSuccess(null, 'Orden actualizado correctamente');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
