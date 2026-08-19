<?php
/** API de autenticación. Compatible con PHP 5. */
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder_login($estado, $datos, $codigo)
{
    if (function_exists('http_response_code')) http_response_code($codigo);
    else header('X-PHP-Response-Code: ' . $codigo, true, $codigo);
    echo json_encode(array_merge(array('ok' => $estado), $datos));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder_login(FALSE, array('message' => 'Método no permitido'), 405);

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
if ($login === '' || $password === '') responder_login(FALSE, array('message' => 'Debes indicar usuario y contraseña'), 400);

include('includes/database.php');
if (!$db) responder_login(FALSE, array('message' => 'No se pudo conectar con la base de datos'), 500);

$authenticated = FALSE;
$passwordHash = md5($password); // Compatibilidad con las contraseñas almacenadas por v3.

if ($login === 'admin') {
    $result = mysqli_query($db, "SELECT valor FROM config WHERE clave='admin' LIMIT 1");
    if ($result && ($row = mysqli_fetch_assoc($result)) && $row['valor'] === $passwordHash) {
        $_SESSION['idUsuario'] = 'admin';
        $_SESSION['rol'] = 'admin';
        $_SESSION['loginUsuario'] = 'admin';
        $authenticated = TRUE;
    }
    if ($result) mysqli_free_result($result);
} else {
    $statement = mysqli_prepare($db, 'SELECT id, nombre, usuario, idEspecialidad, idDepartamento, jefe_departamento FROM profesores WHERE usuario=? AND clave=? AND activo=1 LIMIT 1');
    if ($statement) {
        mysqli_stmt_bind_param($statement, 'ss', $login, $passwordHash);
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);
        if (mysqli_stmt_num_rows($statement) > 0) {
            mysqli_stmt_bind_result($statement, $id, $nombre, $usuario, $especialidad, $departamento, $jefe);
            mysqli_stmt_fetch($statement);
            $_SESSION['idUsuario'] = $id;
            $_SESSION['nombreUsuario'] = $nombre;
            $_SESSION['loginUsuario'] = $usuario;
            if (!empty($especialidad)) $_SESSION['especialidadUsuario'] = $especialidad;
            if (!empty($departamento)) $_SESSION['departamentoUsuario'] = $departamento;
            $_SESSION['rol'] = $jefe ? 'jefeDepartamento' : 'profesor';
            $authenticated = TRUE;
        }
        mysqli_stmt_close($statement);
    }
}

include('includes/database2.php');
if (!$authenticated) responder_login(FALSE, array('message' => 'Usuario o contraseña incorrectos'), 401);

@session_regenerate_id(TRUE);
responder_login(TRUE, array('message' => 'Acceso correcto'), 200);
?>
