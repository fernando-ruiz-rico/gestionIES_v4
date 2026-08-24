<?php
// API endpoint para insertar o actualizar un departamento
// Requiere sesión iniciada y rol de admin
// Recibe: nombre (requerido), id (opcional - si existe actualiza, si no inserta)

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Solo admin (fiel a v3)
checkPermission(array(ROLE_ADMIN));

if (empty($_POST['nombre'])) {
    sendJSONError('El nombre del departamento es requerido', 400);
}

$nombre = $_POST['nombre'];
// id opcional: si llega no vacío es una actualización, si no es una inserción
$id = postOptimoInt('id');

try {
    $db = Db::open();
    if ($id === null) {
        // Insertar nuevo departamento
        $db->execute("INSERT INTO departamentos (nombre) VALUES (?)", $nombre);
        $idNuevo = $db->insertId();
    } else {
        // Actualizar departamento existente
        $db->execute("UPDATE departamentos SET nombre = ? WHERE id = ?", $nombre, $id);
        $idNuevo = $id;
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($id === null) {
    sendJSONSuccess(array('id' => (int)$idNuevo), 'Departamento creado correctamente');
} else {
    sendJSONSuccess(array('id' => (int)$idNuevo), 'Departamento actualizado correctamente');
}
?>
