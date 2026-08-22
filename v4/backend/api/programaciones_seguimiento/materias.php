<?php
// API: Listar materias con programación activa para un profesor (seguimiento de programaciones)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// Admin puede elegir profesor; un profesor usa siempre el suyo
$rol = $session['rol'];
$idProfesorSesion = intval($session['idUsuario']);

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

$stmt = mysqli_prepare($db, "SELECT DISTINCT m.id AS id, m.nombre AS nombreMateria, c.nombre AS nomCurso, m.horas AS horas
                                FROM materias m
                                JOIN cursos c ON c.id = m.idCurso
                                WHERE m.tiene_programacion = 1
                                  AND m.id IN (SELECT seleccion.idMateria FROM seleccion WHERE seleccion.idProfesor = ?)
                                ORDER BY m.nombre");
mysqli_stmt_bind_param($stmt, "i", $idProfesor);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . mysqli_error($db)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$materias = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $materias[] = [
        'id'              => intval($fila['id']),
        'nombre'          => $fila['nombreMateria'],
        'nomCurso'        => $fila['nomCurso'],
        'horas'           => intval($fila['horas'])
    ];
}

mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $materias]);
?>
