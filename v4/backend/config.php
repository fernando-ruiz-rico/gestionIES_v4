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

// Constantes de la aplicación
define('APP_NAME', 'GestionIES');
define('APP_VERSION', '4.0');

// Roles disponibles
define('ROLE_ADMIN', 'admin');
define('ROLE_JEFE_DEPARTAMENTO', 'jefeDepartamento');
define('ROLE_PROFESOR', 'profesor');

// Un usuario "super" es admin o jefe de departamento (misma distinción que en v3)
function esUsuarioSuper($rol) {
    return ($rol == ROLE_ADMIN || $rol == ROLE_JEFE_DEPARTAMENTO);
}

// Función para conectar a la base de datos
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

// Capa fina sobre mysqli (clases Db y DbException). Los endpoints que usen
// Db::open() ya la tienen cargada por este require.
require_once __DIR__ . '/lib/db.php';

// Función para escapar strings (prevención SQL injection básica)
function escapeString($str, $conn) {
    return mysqli_real_escape_string($conn, $str);
}

// Determina el curso académico actual (ej. 2025/2026).
// Copia exacta de cursoActual() de v3 (includes/utilidades.php):
// de septiembre a diciembre de 2026 -> 2026/2027,
// de enero a agosto de 2027 -> 2026/2027
function cursoActual() {
    $fecha = explode("/", date("n/Y"));
    if (intval($fecha[0]) >= 9) {
        $anyo1Curso = intval($fecha[1]);
        $anyo2Curso = $anyo1Curso + 1;
    } else {
        $anyo2Curso = intval($fecha[1]);
        $anyo1Curso = $anyo2Curso - 1;
    }
    return $anyo1Curso . "/" . $anyo2Curso;
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

// Cabecera común de las respuestas JSON de la API: cada endpoint la declara
// al principio (cabeceraJson()), para que el tipo de contenido se cambie
// en un solo sitio
function cabeceraJson() {
    header('Content-Type: application/json; charset=utf-8');
}

// Lee un campo opcional de $_POST: devuelve el valor si llega no vacío, y null si no
function postOptimo($campo) {
    if (isset($_POST[$campo]) && !empty($_POST[$campo])) {
        return $_POST[$campo];
    }
    return null;
}

// Como postOptimo, pero convertido a entero
function postOptimoInt($campo) {
    if (isset($_POST[$campo]) && !empty($_POST[$campo])) {
        return intval($_POST[$campo]);
    }
    return null;
}

// Lee un campo opcional de $_GET: devuelve el valor si llega no vacío, y el
// default (cadena vacía por defecto) si no
function getOptimo($campo, $defecto = '') {
    if (isset($_GET[$campo]) && !empty($_GET[$campo])) {
        return $_GET[$campo];
    }
    return $defecto;
}

// Como getOptimo, pero convertido a entero (default 0 por defecto)
function getOptimoInt($campo, $defecto = 0) {
    if (isset($_GET[$campo]) && !empty($_GET[$campo])) {
        return intval($_GET[$campo]);
    }
    return $defecto;
}

// Lee un campo opcional de un array $datos (el cuerpo de la petición,
// decodificado con cuerpoJson): devuelve el valor si el campo llega, y el
// default (cadena vacía por defecto) si no
function datosOptimo($datos, $campo, $defecto = '') {
    if (isset($datos[$campo])) {
        return $datos[$campo];
    }
    return $defecto;
}

// Como datosOptimo, pero convertido a entero (default 0 por defecto)
function datosOptimoInt($datos, $campo, $defecto = 0) {
    if (isset($datos[$campo])) {
        return intval($datos[$campo]);
    }
    return $defecto;
}

// Lee y decodifica el cuerpo de la petición (php://input) como JSON.
// Devuelve el array decodificado, o un array vacío si el cuerpo es vacío
// o no es JSON válido
function cuerpoJson() {
    $datos = json_decode(file_get_contents('php://input'), true);
    return is_array($datos) ? $datos : array();
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
        // El jefe de departamento no ve el menú "Profesores y Departamentos"
        // (es solo de admin): su acceso a Especialidades es un ítem propio
        // de primer nivel, con la misma vista que la del admin.
        array("id" => 12, "submenu" => false, "texto" => "Especialidades", "roles" => array(ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-diagram-3", "link" => "especialidades"),

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
        array("id" => 3, "submenu" => true, "texto" => "Temas / Unidades", "roles" => null, "icono" => "bi-stack", "link" => "temas"),
        array("id" => 3, "submenu" => true, "texto" => "Cont. defecto unidades", "roles" => array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO), "icono" => "bi-database", "link" => "temas_contenidos_defecto"),
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
