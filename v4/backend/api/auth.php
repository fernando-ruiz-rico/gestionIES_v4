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
    
    $username = escapeString($username, $conn);
    
    // Consulta compatible con PHP 7+ (mysqli)
    $query = "SELECT idUsuario, loginUsuario, password, rol, nombre, apellidos 
              FROM usuarios 
              WHERE loginUsuario = '$username'";
    
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
    
    // Verificar contraseña (asumiendo que está en texto plano o hash MD5 como en v3)
    // Ajustar según cómo esté implementado en v3
    $validPassword = false;
    
    // Intentar con MD5 primero (común en apps antiguas)
    if (md5($password) == $user['password']) {
        $validPassword = true;
    }
    // Si no, comparar directo (texto plano)
    else if ($password == $user['password']) {
        $validPassword = true;
    }
    
    if (!$validPassword) {
        closeDBConnection($conn);
        sendJSONError('Usuario o contraseña incorrectos');
    }
    
    // Iniciar sesión y guardar datos del usuario
    @session_start();
    $_SESSION['idUsuario'] = $user['idUsuario'];
    $_SESSION['loginUsuario'] = $user['loginUsuario'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['apellidos'] = $user['apellidos'];
    
    closeDBConnection($conn);
    
    sendJSONSuccess(array(
        'idUsuario' => $user['idUsuario'],
        'loginUsuario' => $user['loginUsuario'],
        'rol' => $user['rol'],
        'nombre' => $user['nombre'],
        'apellidos' => $user['apellidos']
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
        'apellidos' => $_SESSION['apellidos']
    ));
}
?>
