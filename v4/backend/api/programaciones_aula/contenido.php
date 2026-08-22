<?php
// API: Cargar el texto introductorio de una programación de aula (tema + grupo + profesor)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

$idTema     = isset($_GET['idTema']) ? intval($_GET['idTema']) : 0;
$idGrupo    = isset($_GET['idGrupo']) ? intval($_GET['idGrupo']) : 0;
$rol        = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

// Admin puede ver contenido de cualquier profesor
if (esUsuarioSuper($rol)) {
    $idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : $idUsuarioSesion;
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idGrupo <= 0 || $idProfesor <= 0) {
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

$stmt = mysqli_prepare($db, "SELECT texto FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?");
mysqli_stmt_bind_param($stmt, "iii", $idTema, $idGrupo, $idProfesor);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . mysqli_error($db)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$texto = '';
if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $texto = $fila['texto'];
}

mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($db);

echo json_encode(['success' => true, 'data' => ['texto' => $texto]]);
?>
