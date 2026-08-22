<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Se trae el nombre del curso al que pertenece cada grupo (cursos.nombre)
// para que la vista de grupos muestre el curso en conjunto con cada grupo.
$result = mysqli_query($db, "SELECT g.*, c.nombre AS nombreCurso
                              FROM grupos g
                              LEFT JOIN cursos c ON c.id = g.idCurso
                              ORDER BY g.nombre");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db)]);
    exit;
}

$grupos = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $grupos[] = $fila;
}
mysqli_free_result($result);
mysqli_close($db);
echo json_encode($grupos);
?>
