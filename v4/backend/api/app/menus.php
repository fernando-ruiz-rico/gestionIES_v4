<?php
// Menús y datos de usuario para el sidebar, según el rol de la sesión
require_once '../../config.php';
cabeceraJson();

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$rol = $_SESSION['rol'];
$departamentoUsuario = isset($_SESSION['departamentoUsuario']) ? $_SESSION['departamentoUsuario'] : 0;

$menus = getMenus($rol, $departamentoUsuario);

sendJSONSuccess(array(
    'menus' => $menus,
    'usuario' => array(
        'idUsuario' => $_SESSION['idUsuario'],
        'loginUsuario' => $_SESSION['loginUsuario'],
        'rol' => $rol,
        'departamentoUsuario' => $departamentoUsuario
    )
));
