<?php
/**
 * Configuración de la base de datos y constantes globales
 * Backend para GestionIES v4
 */

// Configuración de la base de datos
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gestionies');
define('DB_USER', 'root');
define('DB_PASS', '');

// Función para conectar a la base de datos con PDO
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        return null;
    }
}

// Constantes de la aplicación
define('APP_NAME', 'GestionIES');
define('APP_VERSION', '4.0');

// Roles disponibles
define('ROLE_ADMIN', 'admin');
define('ROLE_JEFE_DEPARTAMENTO', 'jefeDepartamento');
define('ROLE_PROFESOR', 'profesor');

// Función para conectar a la base de datos (compatible con PHP 7+)
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        return null;
    }
    
    // Establecer charset utf8
    mysqli_set_charset($conn, 'utf8');
    
    return $conn;
}

// Función para cerrar conexión
function closeDBConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

// Función para escapar strings (prevención SQL injection básica)
function escapeString($str, $conn) {
    return mysqli_real_escape_string($conn, $str);
}

// Función para enviar respuesta JSON
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Función para enviar error JSON
function sendJSONError($message, $statusCode = 400) {
    sendJSONResponse(array('success' => false, 'error' => $message), $statusCode);
}

// Función para enviar éxito JSON
function sendJSONSuccess($data, $message = 'Operación realizada correctamente') {
    sendJSONResponse(array('success' => true, 'message' => $message, 'data' => $data));
}

// Comprobar si hay sesión activa
function checkSession() {
    @session_start();
    
    if (empty($_SESSION['idUsuario'])) {
        sendJSONError('No hay sesión activa', 401);
    }
    
    return $_SESSION;
}

// Comprobar permisos de usuario
function checkPermission($requiredRoles) {
    $session = checkSession();
    
    if (!in_array($session['rol'], $requiredRoles)) {
        sendJSONError('No tiene permisos para realizar esta acción', 403);
    }
    
    return $session;
}

// Obtener menus para el sidebar (similar a config.php de v3)
function getMenus($rol, $departamentoUsuario = 0) {
    $super = ($rol == ROLE_ADMIN || $rol == ROLE_JEFE_DEPARTAMENTO);
    
    // Menús basados en config.php de v3
    $menus = array(
        array("id" => 1, "submenu" => false, "texto" => "Profesores y Departamentos", "roles" => array(ROLE_ADMIN), "icono" => "bi-person-badge", "link" => null),
        array("id" => 1, "submenu" => true, "texto" => "Departamentos", "roles" => array(ROLE_ADMIN), "icono" => "bi-archive", "link" => "departamentos"),
        array("id" => 1, "submenu" => true, "texto" => "Especialidades", "roles" => array(ROLE_ADMIN), "icono" => "bi-diagram-3", "link" => "especialidades"),
        array("id" => 1, "submenu" => true, "texto" => "Profesores", "roles" => array(ROLE_ADMIN), "icono" => "bi-person-badge", "link" => "profesores"),
        
        array("id" => 2, "submenu" => false, "texto" => "Cursos y Materias", "roles" => array(ROLE_ADMIN), "icono" => "bi-tree", "link" => null),
        array("id" => 2, "submenu" => true, "texto" => "Ciclos", "roles" => array(ROLE_ADMIN), "icono" => "bi-mortarboard", "link" => "ciclos"),
        array("id" => 2, "submenu" => true, "texto" => "Cursos", "roles" => array(ROLE_ADMIN), "icono" => "bi-tree", "link" => "cursos"),
        array("id" => 2, "submenu" => true, "texto" => "Grupos", "roles" => array(ROLE_ADMIN), "icono" => "bi-people", "link" => "grupos"),
        array("id" => 2, "submenu" => true, "texto" => "Materias", "roles" => array(ROLE_ADMIN), "icono" => "bi-journal-text", "link" => "materias"),
        
        array("id" => 3, "submenu" => false, "texto" => "Programaciones", "roles" => null, "icono" => "bi-file-earmark-text", "link" => null),
        array("id" => 3, "submenu" => true, "texto" => "Apartados PD", "roles" => array(ROLE_ADMIN), "icono" => "bi-list", "link" => "programaciones_apartados"),
        array("id" => 3, "submenu" => true, "texto" => "Contenidos generales", "roles" => array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-database", "link" => "programaciones_contenidos_defecto"),
        array("id" => 3, "submenu" => true, "texto" => "Formación Empresa (RA)", "roles" => null, "icono" => "bi-bar-chart", "link" => "resultados_aprendizaje"),
        array("id" => 3, "submenu" => true, "texto" => "Competencias", "roles" => array(ROLE_ADMIN), "icono" => "bi-lightning", "link" => "competencias_ciclos"),
        array("id" => 3, "submenu" => true, "texto" => "Cualificaciones y UC", "roles" => array(ROLE_ADMIN), "icono" => "bi-award", "link" => "cualificaciones_uc"),
        array("id" => 3, "submenu" => true, "texto" => "Programaciones", "roles" => null, "icono" => "bi-file-earmark-text", "link" => "programaciones"),
        array("id" => 3, "submenu" => true, "texto" => "Programaciones de aula", "roles" => null, "icono" => "bi-house-door", "link" => "programaciones_aula"),
        array("id" => 3, "submenu" => true, "texto" => "Apartados PCCF", "roles" => array(ROLE_ADMIN), "icono" => "bi-list", "link" => "pccf_apartados"),
        array("id" => 3, "submenu" => true, "texto" => "Contenidos grales. PCCF", "roles" => array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-database", "link" => "pccf_contenidos_defecto"),
        array("id" => 3, "submenu" => true, "texto" => "PCCF", "roles" => null, "icono" => "bi-file-earmark-text", "link" => "pccf"),
        array("id" => 3, "submenu" => true, "texto" => "Seguimiento", "roles" => null, "icono" => "bi-graph-up", "link" => "programaciones_seguimiento_aula"),
        
        array("id" => 4, "submenu" => false, "texto" => "Desideratas", "roles" => null, "icono" => "bi-hand-index", "link" => null),
        array("id" => 4, "submenu" => true, "texto" => "Escenarios", "roles" => array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-signpost-split", "link" => "escenarios"),
        array("id" => 4, "submenu" => true, "texto" => "Histórico", "roles" => array(ROLE_PROFESOR, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-clock-history", "link" => "historico"),
        array("id" => 4, "submenu" => true, "texto" => "Selección", "roles" => null, "icono" => "bi-hand-index", "link" => "seleccion"),
        
        array("id" => 5, "submenu" => false, "texto" => "Actas", "roles" => null, "icono" => "bi-book", "link" => "actas"),
        
        array("id" => 7, "submenu" => false, "texto" => "Perfil", "roles" => array(ROLE_PROFESOR, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-person-circle", "link" => "perfil"),
        array("id" => 8, "submenu" => false, "texto" => "Configuración", "roles" => array(ROLE_ADMIN), "icono" => "bi-gear", "link" => "configuracion"),
        array("id" => 9, "submenu" => false, "texto" => "Ayuda", "roles" => null, "icono" => "bi-question-circle", "link" => "ayuda"),
        array("id" => 10, "submenu" => false, "texto" => "Ayuda (Admin)", "roles" => array(ROLE_ADMIN), "icono" => "bi-question-circle", "link" => "ayuda_admin"),
        array("id" => 11, "submenu" => false, "texto" => "Salir", "roles" => null, "icono" => "bi-box-arrow-right", "link" => "logout")
    );
    
    // Filtrar menús según rol
    $filteredMenus = array();
    foreach ($menus as $menu) {
        if ($menu["roles"] === null || in_array($rol, $menu["roles"])) {
            $filteredMenus[] = $menu;
        }
    }
    
    return $filteredMenus;
}
?>
