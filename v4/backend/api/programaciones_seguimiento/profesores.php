<?php
// API: Listar todos los profesores para la selección en el seguimiento de programaciones
// (equivalente a v3 includes/seleccion_profesor.php: todos los profesores, por nombre)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// Solo un administrador o jefe de departamento puede elegir profesor
$rol = $session['rol'];
if (!esUsuarioSuper($rol)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Solo id y nombre (los campos que usa el desplegable); evita devolver clave, e-mail, teléfono...
$result = mysqli_query($db, "SELECT id, nombre FROM profesores ORDER BY nombre");

if (!$result) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al consultar la base de datos: ' . mysqli_error($db)]);
    exit;
}

$profesores = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $profesores[] = ['id' => intval($fila['id']), 'nombre' => $fila['nombre']];
}

mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $profesores]);
?>
