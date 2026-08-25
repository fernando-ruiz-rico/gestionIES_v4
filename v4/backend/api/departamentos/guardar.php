<?php
// API endpoint para insertar o actualizar un departamento
// Requiere sesión iniciada y rol de admin
// Recibe: nombre (requerido), id (opcional - si existe actualiza, si no inserta)

require_once '../../config.php';
cabeceraJson();

// Solo admin (fiel a v3)
checkPermission(array(ROLE_ADMIN));

// El cuerpo llega en JSON (cuerpoJson); la sesión la gestiona checkPermission
$datos = cuerpoJson();
if (empty($datos['nombre'])) {
    sendJSONError('El nombre del departamento es requerido', 400);
}

$nombre = $datos['nombre'];
// idDepartamento opcional: si llega no vacío es una actualización, si no es una inserción
$id = datosOptimoInt($datos, 'idDepartamento');

try {
    $db = Db::open();
    if ($id <= 0) {
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

if ($id <= 0) {
    sendJSONSuccess(array('id' => (int)$idNuevo), 'Departamento creado correctamente');
} else {
    sendJSONSuccess(array('id' => (int)$idNuevo), 'Departamento actualizado correctamente');
}
?>
