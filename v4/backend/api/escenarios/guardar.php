<?php
// API para crear o actualizar un escenario de desideratas, junto con los
// departamentos asociados (fiel a v3/insertar_escenario.php)
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
$nombre = trim(datosOptimo($datos, 'nombre'));
$id = datosOptimoInt($datos, 'id');
// Departamentos elegidos en el formulario (array de ids)
$departamentos = isset($datos['departamentos']) && is_array($datos['departamentos']) ? $datos['departamentos'] : array();

if ($nombre === '') {
    sendJSONError('El nombre es obligatorio', 400);
}
if (count($departamentos) === 0) {
    sendJSONError('El escenario necesita al menos un departamento', 400);
}

try {
    $db = Db::open();
    if ($id > 0) {
        // Actualización: cambia el nombre y reasigna los departamentos
        $db->execute("UPDATE escenarios_desideratas SET nombre=? WHERE id=?", $nombre, $id);
        $db->execute("DELETE FROM departamentos_escenarios WHERE idEscenario=?", $id);
    } else {
        // Alta: crea el escenario (campos por defecto) y se rellenan los departamentos
        $db->execute("INSERT INTO escenarios_desideratas (nombre) VALUES (?)", $nombre);
        $id = $db->insertId();
    }
    foreach ($departamentos as $idDepartamento) {
        $db->execute("INSERT INTO departamentos_escenarios (idEscenario, idDepartamento) VALUES (?, ?)", $id, intval($idDepartamento));
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $id), 'Guardado');
?>
