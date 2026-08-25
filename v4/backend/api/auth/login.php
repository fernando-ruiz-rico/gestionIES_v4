<?php
// API para iniciar sesión (login). Inicia sesión y devuelve los datos del usuario.
require_once '../../config.php';
cabeceraJson();

// El cuerpo llega en JSON (cuerpoJson)
$datos = cuerpoJson();
$username = datosOptimo($datos, 'username');
$password = datosOptimo($datos, 'password');

if (empty($username) || empty($password)) {
    sendJSONError('Usuario y contraseña son requeridos');
}

$db = Db::open();

// Primero miramos si el login es del administrador de la aplicación
// (tabla config, clave 'admin'), como en v3/login.php
$md5pass = md5($password);
try {
    $rowAdm = $db->fetchOne("SELECT valor FROM config WHERE clave='admin' AND valor = ?", $md5pass);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
if ($rowAdm) {
    @session_start();
    session_destroy();
    @session_start();
    $_SESSION['idUsuario'] = 'admin';
    $_SESSION['rol'] = ROLE_ADMIN;
    $_SESSION['loginUsuario'] = 'admin';
    $_SESSION['nombre'] = 'Administrador';

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
try {
    $user = $db->fetchOne("SELECT id, nombre, usuario, clave, idDepartamento, jefe_departamento, activo
                          FROM profesores
                          WHERE usuario = ? AND clave = ? AND activo = 1",
        $username, $md5pass);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if (!$user) {
    sendJSONError('Usuario o contraseña incorrectos');
}

// Iniciar sesión y guardar datos del usuario (misma lógica de roles que v3/login.php)
@session_start();
$_SESSION['idUsuario'] = $user['id'];
$_SESSION['loginUsuario'] = $user['usuario'];
$_SESSION['nombre'] = $user['nombre'];
if ($user['jefe_departamento']) {
    $_SESSION['rol'] = ROLE_JEFE_DEPARTAMENTO;
} else {
    $_SESSION['rol'] = ROLE_PROFESOR;
}
if (!empty($user['idEspecialidad'])) {
    $_SESSION['especialidadUsuario'] = $user['idEspecialidad'];
}
if (!empty($user['idDepartamento'])) {
    $_SESSION['departamentoUsuario'] = $user['idDepartamento'];
}
$_SESSION['idDepartamento'] = $user['idDepartamento'];
$_SESSION['activo'] = $user['activo'];

sendJSONSuccess(array(
    'idUsuario' => $user['id'],
    'loginUsuario' => $user['usuario'],
    'rol' => $_SESSION['rol'],
    'nombre' => $user['nombre'],
    'idDepartamento' => $user['idDepartamento']
), 'Login correcto');
