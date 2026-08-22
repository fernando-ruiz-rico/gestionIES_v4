<?php
// API: Listar grupos de un profesor para una materia (seguimiento de programaciones)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

$idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
$rol = $session['rol'];
$idProfesorSesion = intval($session['idUsuario']);

if ($idMateria <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Debe indicar una materia']);
    exit;
}

// Admin puede ver grupos de cualquier profesor
if (esUsuarioSuper($rol)) {
    $idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : $idProfesorSesion;
} else {
    $idProfesor = $idProfesorSesion;
}

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT g.id AS id, g.nombre AS nombre
                                FROM grupos g
                                WHERE g.id IN (
                                    SELECT s.idGrupo FROM seleccion s
                                    JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                    WHERE s.idMateria = ? AND s.idProfesor = ? AND e.actual = 1
                                )
                                ORDER BY g.nombre");
mysqli_stmt_bind_param($stmt, "ii", $idMateria, $idProfesor);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . mysqli_error($db)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$grupos = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $grupos[] = [
        'id'      => intval($fila['id']),
        'nombre'  => $fila['nombre']
    ];
}

mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $grupos]);
?>
