<?php
/** Estado de sesión y menú autorizado para el frontend Vue. */
chdir(dirname(__DIR__));
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['idUsuario'])) {
    if (function_exists('http_response_code')) http_response_code(401);
    else header('X-PHP-Response-Code: 401', true, 401);
    echo json_encode(array('authenticated' => FALSE, 'message' => 'La sesión ha caducado'));
    exit;
}

include('includes/comprobar_activaciones.php');
include('includes/config.php');

$role = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$filteredMenus = array();
foreach ($menus as $menu) {
    if ($menu['roles'] === NULL || in_array($role, $menu['roles'])) $filteredMenus[] = $menu;
}

echo json_encode(array(
    'authenticated' => TRUE,
    'id' => $_SESSION['idUsuario'],
    'login' => isset($_SESSION['loginUsuario']) ? $_SESSION['loginUsuario'] : '',
    'name' => isset($_SESSION['nombreUsuario']) ? $_SESSION['nombreUsuario'] : '',
    'role' => $role,
    'department' => isset($_SESSION['departamentoUsuario']) ? $_SESSION['departamentoUsuario'] : NULL,
    'specialty' => isset($_SESSION['especialidadUsuario']) ? $_SESSION['especialidadUsuario'] : NULL,
    'menus' => $filteredMenus,
    'features' => array('programaciones' => $programacionesActivadas, 'desideratas' => $desideratasActivadas)
));
?>
