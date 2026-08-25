<?php
// API para cerrar sesión (logout)
require_once '../../config.php';
cabeceraJson();

@session_start();
session_destroy();
sendJSONSuccess(null, 'Sesión cerrada correctamente');
