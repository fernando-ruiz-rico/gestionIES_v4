<?php
// API para crear o modificar un curso (Fase 1)
// Equivalente a v3/ajax/cursos/insertar_curso.php
require_once '../../config.php';
cabeceraJson();

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
$nombre      = trim(datosOptimo($datos, 'nombre'));
$abreviatura = trim(datosOptimo($datos, 'abreviatura'));
$horas       = datosOptimoInt($datos, 'horas_semana');
$categoria   = trim(datosOptimo($datos, 'categoria'));
$id          = datosOptimoInt($datos, 'id');

if ($nombre === '' || $abreviatura === '') {
    sendJSONError('Faltan datos obligatorios (nombre y abreviatura)', 400);
}

// En v3 el campo "horas semanales" puede llegar vacío; en ese caso se guarda 0
try {
    $db = Db::open();
    if ($id > 0) {
        $db->execute("UPDATE cursos SET nombre = ?, abreviatura = ?, horas_semana = ?, categoria = ? WHERE id = ?", $nombre, $abreviatura, $horas, $categoria, $id);
        $nuevoId = $id;
    } else {
        $db->execute("INSERT INTO cursos (nombre, abreviatura, horas_semana, categoria) VALUES (?, ?, ?, ?)", $nombre, $abreviatura, $horas, $categoria);
        $nuevoId = $db->insertId();
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => (int)$nuevoId), 'Curso guardado correctamente');
?>
