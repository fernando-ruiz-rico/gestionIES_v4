<?php
/**
 * API para autenticación de usuarios
 * Backend para GestionIES v4
 */

require_once '../config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        sendJSONError('Acción no válida');
}

function handleLogin() {
    $conn = getDBConnection();
    
    if (!$conn) {
        sendJSONError('Error de conexión a la base de datos');
    }
    
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        closeDBConnection($conn);
        sendJSONError('Usuario y contraseña son requeridos');
    }
    
    // Primero miramos si el login es del administrador de la aplicación
    // (tabla config, clave 'admin'), como en v3/login.php
    $md5pass = md5($password);
    $queryAdm = mysqli_query($conn, "SELECT valor FROM config WHERE clave='admin' AND valor = '" . $md5pass . "'");
    if ($queryAdm && mysqli_num_rows($queryAdm) > 0) {
        @session_start();
        session_destroy();
        @session_start();
        $_SESSION['idUsuario'] = 'admin';
        $_SESSION['rol'] = 'admin';
        $_SESSION['loginUsuario'] = 'admin';
        $_SESSION['nombre'] = 'Administrador';

        closeDBConnection($conn);
        sendJSONSuccess(array(
            'idUsuario' => 'admin',
            'loginUsuario' => 'admin',
            'rol' => 'admin',
            'nombre' => 'Administrador',
            'idDepartamento' => null
        ), 'Login correcto');
        return;
    }
    
    // Si no, permitimos entrar a profesores con credenciales correctas y actualmente activos
    $username = escapeString($username, $conn);
    $query = "SELECT id, nombre, usuario, clave, idDepartamento, jefe_departamento, activo 
              FROM profesores 
              WHERE usuario = '$username' AND clave = '" . $md5pass . "' AND activo = 1";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        closeDBConnection($conn);
        sendJSONError('Error en la consulta');
    }
    
    if (mysqli_num_rows($result) == 0) {
        closeDBConnection($conn);
        sendJSONError('Usuario o contraseña incorrectos');
    }
    
    $user = mysqli_fetch_assoc($result);
    
    // Iniciar sesión y guardar datos del usuario (misma lógica de roles que v3/login.php)
    @session_start();
    $_SESSION['idUsuario'] = $user['id'];
    $_SESSION['loginUsuario'] = $user['usuario'];
    $_SESSION['nombre'] = $user['nombre'];
    if ($user['jefe_departamento']) {
        $_SESSION['rol'] = 'jefeDepartamento';
    } else {
        $_SESSION['rol'] = 'profesor';
    }
    if (!empty($user['idEspecialidad'])) {
        $_SESSION['especialidadUsuario'] = $user['idEspecialidad'];
    }
    if (!empty($user['idDepartamento'])) {
        $_SESSION['departamentoUsuario'] = $user['idDepartamento'];
    }
    $_SESSION['idDepartamento'] = $user['idDepartamento'];
    $_SESSION['activo'] = $user['activo'];

    closeDBConnection($conn);
    
    sendJSONSuccess(array(
        'idUsuario' => $user['id'],
        'loginUsuario' => $user['usuario'],
        'rol' => $_SESSION['rol'],
        'nombre' => $user['nombre'],
        'idDepartamento' => $user['idDepartamento']
    ), 'Login correcto');
}

function handleLogout() {
    @session_start();
    session_destroy();
    sendJSONSuccess(null, 'Sesión cerrada correctamente');
}

function checkAuth() {
    @session_start();
    
    if (empty($_SESSION['idUsuario'])) {
        sendJSONError('No hay sesión activa', 401);
    }
    
    sendJSONSuccess(array(
        'idUsuario' => $_SESSION['idUsuario'],
        'loginUsuario' => $_SESSION['loginUsuario'],
        'rol' => $_SESSION['rol'],
        'nombre' => $_SESSION['nombre'],
        'idDepartamento' => isset($_SESSION['idDepartamento']) ? $_SESSION['idDepartamento'] : null
    ));
}
?>
