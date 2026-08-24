<?php
// API endpoint para ordenar profesores de un departamento
// Requiere sesión iniciada y rol de admin o jefeDepartamento
// Recibe: orden (cadena con ids separados por comas, prefijo "pr")
// Devuelve: success (true/false), mensaje

require_once '../../config.php';
cabeceraJson();
session_start();

// Verificar permisos (admin o jefe de departamento)
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

// Campo único del endpoint: postOptimo devuelve el valor si llega no vacío, y null si no
$orden = postOptimo('orden');
if ($orden === null) {
    sendJSONError('Orden no proporcionado', 400);
}

// Lo que se recibe en el parámetro "orden" son los id de los profesores en el orden en que
// se quieren asignar. Cada profesor en el listado viene con id "pr" seguido de su código.
$partes = explode(",", $orden);
try {
    $db = Db::open();
    for ($i = 1; $i <= count($partes); $i++) {
        // Quitar prefijo "pr" para obtener el código del profesor
        $codProfesor = substr($partes[$i-1], 2);
        $db->execute("UPDATE profesores SET orden = ? WHERE id = ?", $i, $codProfesor);
    }
} catch (DbException $e) {
    sendJSONError('Error al actualizar el orden de los profesores: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('mensaje' => 'Orden de profesores actualizado correctamente'));
?>
