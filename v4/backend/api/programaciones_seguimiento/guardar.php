<?php
// API: Guardar los datos de seguimiento de una programación de aula
// (equivalente a v3 ajax/programaciones_seguimiento/insertar_seguimiento_programacion_aula.php)
// Inserta o actualiza la fila del triplete materia+grupo+profesor en el curso actual;
// con textos vacíos se guarda el texto vacío, idéntico al comportamiento de v3.
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$session = $_SESSION;

if (empty($session['idUsuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
    exit;
}

// En v3 el guardado está permitido a admin/jefe (para cualquier profesor) y a un profesor para sí mismo
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if ($rol === 'admin' || $rol === 'jefeDepartamento') {
    // Admin puede guardar para cualquier profesor
} else {
    // Un profesor solo puede guardar el seguimiento de sí mismo
    if (isset($session['activo']) && $session['activo'] == 1) {
        // Ok, continuar
    } else {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
        exit;
    }
}

$data = json_decode(file_get_contents('php://input'), true);

$idMateria      = isset($data['idMateria']) ? intval($data['idMateria']) : 0;
$idGrupo        = isset($data['idGrupo']) ? intval($data['idGrupo']) : 0;
$idEvaluacion   = isset($data['idEvaluacion']) ? intval($data['idEvaluacion']) : 0;
$temporalizacion = isset($data['temporalizacion']) ? $data['temporalizacion'] : '';
$resultados      = isset($data['resultados']) ? $data['resultados'] : '';
$inclusion       = isset($data['inclusion']) ? $data['inclusion'] : '';
$numAprobados   = isset($data['num_aprobados']) ? intval($data['num_aprobados']) : 0;
$numSuspensos   = isset($data['num_suspensos']) ? intval($data['num_suspensos']) : 0;
$numOtros       = isset($data['num_otros']) ? intval($data['num_otros']) : 0;

// Determinar idProfesor según rol
if ($rol === 'admin' || $rol === 'jefeDepartamento') {
    $idProfesor = isset($data['idProfesor']) ? intval($data['idProfesor']) : $idUsuarioSesion;
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idMateria <= 0 || $idGrupo <= 0 || $idEvaluacion <= 0 || $idProfesor <= 0) {
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

// Verificar si ya existe una fila para esta combinación (materia + grupo + profesor + curso + evaluación)
$stmtCheck = mysqli_prepare($db, "SELECT id FROM seguimiento_programaciones_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?");
mysqli_stmt_bind_param($stmtCheck, "iisii", $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion);

if (!mysqli_stmt_execute($stmtCheck)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al verificar registro: ' . mysqli_error($db)]);
    exit;
}

$resultCheck = mysqli_stmt_get_result($stmtCheck);
$existe = mysqli_num_rows($resultCheck) > 0;

if ($existe) {
    $stmt = mysqli_prepare($db, "UPDATE seguimiento_programaciones_aula
                                SET temporalizacion = ?, resultados = ?, inclusion = ?, num_aprobados = ?, num_suspensos = ?, num_otros = ?
                                WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?");
    mysqli_stmt_bind_param($stmt, "sssiiiiiisi", $temporalizacion, $resultados, $inclusion, $numAprobados, $numSuspensos, $numOtros, $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO seguimiento_programaciones_aula (idMateria, idGrupo, idProfesor, curso, evaluacion, temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiisisssiii", $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion, $temporalizacion, $resultados, $inclusion, $numAprobados, $numSuspensos, $numOtros);
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_free_result($resultCheck);
    mysqli_close($db);
    echo json_encode(['success' => true, 'message' => 'Seguimiento guardado correctamente']);
} else {
    $error = mysqli_error($db);
    mysqli_stmt_close($stmt);
    mysqli_free_result($resultCheck);
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $error]);
}
?>
