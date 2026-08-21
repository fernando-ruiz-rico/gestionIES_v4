<?php
// ============================================================================
// FASE 2.7 — Contenidos por defecto de temas / unidades de programación
// Equivalente a v3: tems_contenidos_defecto.php + ajax/temas_contenidos_defecto/
//   - cargar   : carga los contenidos por defecto de un departamento
//                (contexto, recursos, metodología, acciones)
//   - guardar  : inserta o actualiza la fila del departamento (rol admin o
//                jefe de departamento; este último solo para su propio depto)
// Modelo fiel a v3: no hay borrado por campo. La fila es por departamento
// (contenidos_defcto_temas.idDepartamento = PK). Se inserta si no existe la
// fila, se actualiza si existe.
// Compatible con PHP 5 (mysqli_*, sentencias preparadas).
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

@session_start();
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$session = $_SESSION;
$permisos = in_array($session['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$body = array();
if ($method === 'POST') {
    $decoded = json_decode(file_get_contents('php://input'), true);
    if (is_array($decoded)) {
        $body = $decoded;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === '' && isset($body['action'])) {
    $action = $body['action'];
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

// ---------------------------------------------------------------------------
// Acción: cargar (GET ?idDepartamento=N)
// ---------------------------------------------------------------------------
if ($action === 'cargar') {
    $idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;
    if ($idDepartamento <= 0) {
        sendJSONError('Debe indicar un departamento', 400);
    }

    $stmt = mysqli_prepare($db, "SELECT contexto, recursos, metodologia, adaptaciones
                FROM contenidos_defcto_temas WHERE idDepartamento = ?");
    if (!$stmt) {
        sendJSONError('Error al preparar la consulta: ' . mysqli_error($db), 500);
    }
    mysqli_stmt_bind_param($stmt, "i", $idDepartamento);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_free_result($result);

    sendJSONSuccess(array(
        'contexto' => $fila ? $fila['contexto'] : '',
        'recursos' => $fila ? $fila['recursos'] : '',
        'metodologia' => $fila ? $fila['metodologia'] : '',
        'adaptaciones' => $fila ? $fila['adaptaciones'] : ''
    ));
}

// ---------------------------------------------------------------------------
// Acción: guardar (POST {idDepartamento, contexto, recursos, metodologia, acciones})
// ---------------------------------------------------------------------------
if ($action === 'guardar') {
    $idDepartamento = isset($body['idDepartamento']) ? intval($body['idDepartamento']) : 0;
    $contexto = isset($body['contexto']) ? $body['contexto'] : '';
    $recursos = isset($body['recursos']) ? $body['recursos'] : '';
    $metodologia = isset($body['metodologia']) ? $body['metodologia'] : '';
    $adaptaciones = isset($body['adaptaciones']) ? $body['adaptaciones'] : '';

    // Un jefe de departamento solo puede editar su propio departamento
    if ($session['rol'] === ROLE_JEFE_DEPARTAMENTO && intval($session['idDepartamento']) !== $idDepartamento) {
        sendJSONError('Solo puede editar el contenido de su propio departamento', 403);
    }
    if ($idDepartamento <= 0) {
        sendJSONError('Debe indicar un departamento', 400);
    }

    // Comprobar si ya existe la fila del departamento
    $stmtCheck = mysqli_prepare($db, "SELECT idDepartamento FROM contenidos_defcto_temas WHERE idDepartamento = ?");
    mysqli_stmt_bind_param($stmtCheck, "i", $idDepartamento);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    $existe = mysqli_num_rows($resultCheck) > 0;
    mysqli_stmt_close($stmtCheck);
    mysqli_free_result($resultCheck);

    if ($existe) {
        $stmt = mysqli_prepare($db, "UPDATE contenidos_defcto_temas SET contexto = ?, recursos = ?, metodologia = ?, adaptaciones = ? WHERE idDepartamento = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $contexto, $recursos, $metodologia, $adaptaciones, $idDepartamento);
    } else {
        $stmt = mysqli_prepare($db, "INSERT INTO contenidos_defcto_temas (idDepartamento, contexto, recursos, metodologia, adaptaciones) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssi", $idDepartamento, $contexto, $recursos, $metodologia, $adaptaciones);
    }

    if (!mysqli_stmt_execute($stmt)) {
        sendJSONError('Error al guardar: ' . mysqli_error($db), 500);
    }
    mysqli_stmt_close($stmt);

    sendJSONSuccess(null, 'Contenidos guardados correctamente');
}

sendJSONError('Acción no válida', 400);
