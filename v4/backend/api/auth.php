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
    
    // Consulta compatible con PHP 5+ (tabla profesores, no usuarios)
    $query = "SELECT id, nombre, usuario, clave, idDepartamento, jefe_departamento, activo 
              FROM profesores 
              WHERE usuario = '$username' AND activo = 1";
    
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
    
    // Verificar contraseña (MD5 como en v3)
    $validPassword = false;
    
    // Intentar con MD5 primero (común en apps antiguas)
    if (md5($password) == $user['clave']) {
        $validPassword = true;
    }
    // Si no, comparar directo (texto plano)
    else if ($password == $user['clave']) {
        $validPassword = true;
    }
    
    if (!$validPassword) {
        closeDBConnection($conn);
        sendJSONError('Usuario o contraseña incorrectos');
    }
    
    // Iniciar sesión y guardar datos del usuario
    @session_start();
    $_SESSION['idUsuario'] = $user['id'];
    $_SESSION['loginUsuario'] = $user['usuario'];
    $_SESSION['rol'] = ($user['jefe_departamento'] == 1) ? 'admin' : 'profesor';
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['idDepartamento'] = $user['idDepartamento'];
    $_SESSION['activo'] = $user['activo'];

    closeDBConnection($conn);
    
    sendJSONSuccess(array(
        'idUsuario' => $user['id'],
        'loginUsuario' => $user['usuario'],
        'rol' => ($_SESSION['rol'] == 'admin') ? 'admin' : 'profesor',
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
