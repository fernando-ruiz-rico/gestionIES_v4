<?php
/**
 * API para obtener menús y configuración de la aplicación
 * Backend para GestionIES v4
 */

require_once '../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'menus';

switch ($action) {
    case 'menus':
        getMenusAPI();
        break;
    case 'activaciones':
        getActivaciones();
        break;
    default:
        sendJSONError('Acción no válida');
}

function getMenusAPI() {
    $rol = $_SESSION['rol'];
    $departamentoUsuario = isset($_SESSION['departamentoUsuario']) ? $_SESSION['departamentoUsuario'] : 0;

    $menus = getMenus($rol, $departamentoUsuario);

    sendJSONSuccess(array(
        'menus' => $menus,
        'usuario' => array(
            'idUsuario' => $_SESSION['idUsuario'],
            'loginUsuario' => $_SESSION['loginUsuario'],
            'rol' => $_SESSION['rol'],
            'departamentoUsuario' => $departamentoUsuario
        )
    ));
}

function getActivaciones() {
    $conn = getDBConnection();

    if (!$conn) {
        sendJSONError('Error de conexión a la base de datos');
    }
    $db = new Db($conn);

    // Estado de las activaciones, igual que includes/comprobar_activaciones.php de v3:
    // la tabla real es 'config' con columnas 'clave' y 'valor' ('ON' / 'OFF').
    $activaciones = array(
        'desideratas' => false,
        'programaciones' => false
    );

    try {
        $filas = $db->fetchAll("SELECT clave, valor FROM config WHERE clave IN ('desideratas', 'programaciones')");
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage());
    }

    foreach ($filas as $row) {
        if ($row['clave'] == 'desideratas') {
            $activaciones['desideratas'] = ($row['valor'] == 'ON');
        }
        if ($row['clave'] == 'programaciones') {
            $activaciones['programaciones'] = ($row['valor'] == 'ON');
        }
    }

    sendJSONSuccess($activaciones);
}
?>
