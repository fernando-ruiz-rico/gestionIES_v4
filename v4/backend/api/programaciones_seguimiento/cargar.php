<?php
// API: Cargar los datos de seguimiento de una programación de aula
// (equivalente a v3 ajax/programaciones_seguimiento/cargar_datos_seguimiento_aula.php)
// Curso: el actual, calculado en el servidor igual que cursoActual() de v3
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$session = $_SESSION;

if (empty($session['idUsuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
    exit;
}

$idMateria    = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
$idGrupo      = isset($_GET['idGrupo']) ? intval($_GET['idGrupo']) : 0;
$idEvaluacion = isset($_GET['idEvaluacion']) ? intval($_GET['idEvaluacion']) : 0;
$rol          = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if ($idMateria <= 0 || $idGrupo <= 0 || $idEvaluacion <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Debe indicar materia, grupo y evaluación']);
    exit;
}

// Admin puede ver seguimiento de cualquier profesor
if ($rol === 'admin' || $rol === 'jefeDepartamento') {
    $idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : $idUsuarioSesion;
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idProfesor <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros no válidos']);
    exit;
}

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$curso = cursoActual();

$stmt = mysqli_prepare($db, "SELECT temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros
                                FROM seguimiento_programaciones_aula
                                WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?");
mysqli_stmt_bind_param($stmt, "iisss", $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . mysqli_error($db)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$data = [
    'temporalizacion' => '',
    'resultados'      => '',
    'inclusion'       => '',
    'num_aprobados'   => 0,
    'num_suspensos'   => 0,
    'num_otros'       => 0
];

if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $data = [
        'temporalizacion' => $fila['temporalizacion'],
        'resultados'      => $fila['resultados'],
        'inclusion'       => $fila['inclusion'],
        'num_aprobados'   => intval($fila['num_aprobados']),
        'num_suspensos'   => intval($fila['num_suspensos']),
        'num_otros'       => intval($fila['num_otros'])
    ];
}

mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => $data]);
?>
