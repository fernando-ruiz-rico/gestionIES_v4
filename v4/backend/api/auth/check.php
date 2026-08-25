<?php
// API para comprobar si hay sesión activa y devolver los datos del usuario
require_once '../../config.php';
cabeceraJson();

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
