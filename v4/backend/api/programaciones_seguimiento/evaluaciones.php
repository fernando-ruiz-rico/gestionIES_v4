<?php
// API: Listar las evaluaciones disponibles (seguimiento de programaciones)
// Equivalente a v3 includes/cargar_evaluaciones.php
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

try {
    $db = Db::open();

    $filas = $db->fetchAll("SELECT id, nombre FROM evaluaciones ORDER BY id");

    $evaluaciones = [];
    foreach ($filas as $fila) {
        $evaluaciones[] = [
            'id'      => intval($fila['id']),
            'nombre'  => $fila['nombre']
        ];
    }

    sendJSONSuccess($evaluaciones);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
