<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Se trae el nombre del curso al que pertenece cada grupo (cursos.nombre)
// para que la vista de grupos muestre el curso en conjunto con cada grupo.
try {
    $db = Db::open();
    $grupos = $db->fetchAll("SELECT g.*, c.nombre AS nombreCurso
                              FROM grupos g
                              LEFT JOIN cursos c ON c.id = g.idCurso
                              ORDER BY g.nombre");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($grupos);
?>
