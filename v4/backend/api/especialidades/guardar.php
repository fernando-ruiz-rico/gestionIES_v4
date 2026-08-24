<?php
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$id = trim(datosOptimo($datos, 'id'));
$descripcion = trim(datosOptimo($datos, 'descripcion'));
$idDepartamento = datosOptimoInt($datos, 'idDepartamento');
$horasTutoria = datosOptimoInt($datos, 'horasTutoria');
$horasIngles = datosOptimoInt($datos, 'horasIngles');

if (empty($id)) {
    sendJSONError('ID obligatorio', 400);
}

if (empty($descripcion)) {
    sendJSONError('Descripción obligatoria', 400);
}

try {
    $db = Db::open();

    // Verificar si existe para actualizar o insertar
    $existe = $db->fetchOne("SELECT id FROM especialidades WHERE id = ?", $id);

    if ($existe) {
        $db->execute("UPDATE especialidades SET descripcion=?, idDepartamento=?, horasTutoria=?, horasIngles=? WHERE id=?", $descripcion, $idDepartamento, $horasTutoria, $horasIngles, $id);
    } else {
        $db->execute("INSERT INTO especialidades (id, descripcion, idDepartamento, horasTutoria, horasIngles) VALUES (?, ?, ?, ?, ?)", $id, $descripcion, $idDepartamento, $horasTutoria, $horasIngles);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $id), 'Guardado correctamente');
?>
