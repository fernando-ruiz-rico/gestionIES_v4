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
    
    // Obtener configuraciones de activación (similar a comprobar_activaciones.php de v3)
    $activaciones = array(
        'desideratasActivadas' => false,
        'programacionesActivadas' => true
    );
    
    // Consultar tabla de configuración si existe
    $query = "SELECT nombre, valor FROM configuracion WHERE nombre IN ('desideratas_activadas', 'programaciones_activadas')";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['nombre'] == 'desideratas_activadas') {
                $activaciones['desideratasActivadas'] = ($row['valor'] == '1' || $row['valor'] == 'true');
            }
            if ($row['nombre'] == 'programaciones_activadas') {
                $activaciones['programacionesActivadas'] = ($row['valor'] == '1' || $row['valor'] == 'true');
            }
        }
    }
    
    closeDBConnection($conn);
    
    sendJSONSuccess($activaciones);
}
?>
