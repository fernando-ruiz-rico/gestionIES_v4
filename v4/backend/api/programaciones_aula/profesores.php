<?php
// API: Listar todos los profesores para la selección en programaciones de aula
// (equivalente a v3 includes/seleccion_profesor.php: todos los profesores, por nombre)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// Solo un administrador o jefe de departamento puede elegir profesor
$rol = $session['rol'];
if (!esUsuarioSuper($rol)) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

try {
    $db = Db::open();

    // Solo id y nombre (los campos que usa el desplegable); evita devolver clave, e-mail, teléfono...
    $filas = $db->fetchAll("SELECT id, nombre FROM profesores ORDER BY nombre");

    $profesores = array();
    foreach ($filas as $fila) {
        $profesores[] = array('id' => intval($fila['id']), 'nombre' => $fila['nombre']);
    }

    sendJSONSuccess($profesores);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
