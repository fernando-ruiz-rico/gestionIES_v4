<?php
// API endpoint para asignar/quitar jefe de departamento a un profesor
// Requiere sesión iniciada y rol de admin
// Devuelve: success (true/false), mensaje

require_once '../../config.php';
cabeceraJson();

// Verificar permisos de administrador
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
if (empty($datos['idProfesor']) || empty($datos['idDepartamento'])) {
    sendJSONError('ID de profesor y departamento son requeridos', 400);
}

$idProfesor = datosOptimoInt($datos, 'idProfesor');
$idDepartamento = datosOptimoInt($datos, 'idDepartamento');

try {
    $db = Db::open();

    // Asignar/Quitar jefe de departamento (toggle 1 - jefe_departamento)
    $db->execute("UPDATE profesores SET jefe_departamento = 1 - jefe_departamento WHERE id = ?", $idProfesor);
} catch (DbException $e) {
    sendJSONError('Error al actualizar el jefe de departamento: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('mensaje' => 'Jefe de departamento actualizado correctamente'));
?>
