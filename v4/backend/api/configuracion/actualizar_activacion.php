<?php
// API de Configuración: activa/desactiva un período (desideratas / programaciones)
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();

try {
    $db = Db::open();

    // El frontend envía "evaluacionRA" (fila programaciones) o "seleccion"
    // (fila desideratas); también aceptamos los nombres de las filas.
    $clave = datosOptimo($datos, 'clave');
    $valor = datosOptimo($datos, 'valor');
    $claves = array('evaluacionRA' => 'programaciones', 'seleccion' => 'desideratas', 'programaciones' => 'programaciones', 'desideratas' => 'desideratas');
    if (!isset($claves[$clave])) {
        throw new Exception('Clave de activación no válida');
    }
    $clave = $claves[$clave];

    // Aceptamos el valor como booleano JSON o como texto ON/OFF
    if ($valor === true || $valor === 'ON' || $valor === '1' || $valor === 'true') {
        $activo = 'ON';
    } elseif ($valor === false || $valor === 'OFF' || $valor === '0' || $valor === 'false') {
        $activo = 'OFF';
    } else {
        throw new Exception('Valor de activación no válido');
    }

    $db->execute("UPDATE config SET valor=? WHERE clave=?", $activo, $clave);
    $db->close();

    sendJSONSuccess(array('clave' => $clave, 'valor' => $activo), 'Activación actualizada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
