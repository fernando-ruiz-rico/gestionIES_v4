<?php
// API para crear o modificar un ciclo formativo (Fase 1)
// Equivalente a v3/ajax/ciclos/insertar_ciclo.php
require_once '../../config.php';
cabeceraJson();

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$nombre   = trim(datosOptimo($datos, 'nombre'));
$familia  = trim(datosOptimo($datos, 'familia'));
$nivel   = trim(datosOptimo($datos, 'nivel'));
$idCiclo = datosOptimoInt($datos, 'id');

if ($nombre === '' || $familia === '' || $nivel === '') {
    sendJSONError('Faltan datos obligatorios (nombre, familia y nivel)', 400);
}

try {
    $db = Db::open();
    if ($idCiclo > 0) {
        $db->execute("UPDATE ciclos SET nombre = ?, familia = ?, nivel = ? WHERE id = ?", $nombre, $familia, $nivel, $idCiclo);
        $nuevoId = $idCiclo;
    } else {
        // La columna "horas" es NOT NULL sin valor por defecto en la tabla;
        // v3 no la pide en el formulario, así que se guarda 0
        $db->execute("INSERT INTO ciclos (nombre, familia, nivel, horas) VALUES (?, ?, ?, 0)", $nombre, $familia, $nivel);
        $nuevoId = $db->insertId();
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => (int)$nuevoId), 'Ciclo guardado correctamente');
?>
