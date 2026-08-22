<?php
// API: Guardar el texto introductorio de una programación de aula
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// En v3 el guardado solo está permitido con permisos (admin/jefe) o un profesor para sí mismo.
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if (esUsuarioSuper($rol)) {
    // Admin puede guardar para cualquier profesor
} else {
    // Un profesor solo puede guardar el contenido de sí mismo
    if (isset($session['activo']) && $session['activo'] == 1) {
        // Ok, continuar
    } else {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
        exit;
    }
}

$data = json_decode(file_get_contents('php://input'), true);

$idTema     = isset($data['idTema']) ? intval($data['idTema']) : 0;
$idGrupo    = isset($data['idGrupo']) ? intval($data['idGrupo']) : 0;
$texto      = isset($data['texto']) ? $data['texto'] : '';

// Determinar idProfesor según rol
if (esUsuarioSuper($rol)) {
    $idProfesor = isset($data['idProfesor']) ? intval($data['idProfesor']) : $idUsuarioSesion;
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

$texto = trim($texto);

// Verificar si ya existe una fila para este triplete (tema + grupo + profesor)
$stmtCheck = mysqli_prepare($db, "SELECT id FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?");
mysqli_stmt_bind_param($stmtCheck, "iii", $idTema, $idGrupo, $idProfesor);

if (!mysqli_stmt_execute($stmtCheck)) {
    mysqli_close($db);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al verificar registro: ' . mysqli_error($db)]);
    exit;
}

$resultCheck = mysqli_stmt_get_result($stmtCheck);
$existe = mysqli_num_rows($resultCheck) > 0;

if ($texto === '') {
    // Borrar el contenido si está vacío (mismo comportamiento que v3: insertar_contenido_programacion.php)
    if ($existe) {
        $stmtDel = mysqli_prepare($db, "DELETE FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?");
        mysqli_stmt_bind_param($stmtDel, "iii", $idTema, $idGrupo, $idProfesor);

        if (mysqli_stmt_execute($stmtDel)) {
            echo json_encode(['success' => true, 'message' => 'Contenido eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . mysqli_error($db)]);
        }

        mysqli_stmt_close($stmtDel);
    } else {
        // No había nada que borrar
        echo json_encode(['success' => true, 'message' => 'No hay contenido para eliminar']);
    }
} else {
    if ($existe) {
        $stmt = mysqli_prepare($db, "UPDATE programaciones_aula_temas SET texto = ? WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?");
        mysqli_stmt_bind_param($stmt, "siii", $texto, $idTema, $idGrupo, $idProfesor);
    } else {
        $stmt = mysqli_prepare($db, "INSERT INTO programaciones_aula_temas (idTema, idGrupo, idProfesor, texto) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiis", $idTema, $idGrupo, $idProfesor, $texto);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Contenido guardado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . mysqli_error($db)]);
    }

    mysqli_stmt_close($stmt);
}

mysqli_stmt_close($stmtCheck);
mysqli_free_result($resultCheck);
mysqli_close($db);
?>
