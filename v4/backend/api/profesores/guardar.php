<?php
// API endpoint para insertar o actualizar un profesor
// Requiere sesión iniciada y rol de admin (o el propio profesor si es su perfil)
// Recibe: nombre, idDepartamento, idEspecialidad (requeridos), abreviatura, usuario, clave, telefono, email, observaciones, prefRojas, prefAmarillas (opcionales)
// Devuelve: success (true/false), mensaje

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Verificar permisos
$permisos = (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') || 
            (!empty($_SESSION['idUsuario']) && !empty($_POST['id']) && $_SESSION['idUsuario'] == $_POST['id']);

if (!$permisos) {
    http_response_code(403);
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

// Validar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['idDepartamento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre y departamento son requeridos']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Escapar datos
$idDepartamento = intval($_POST['idDepartamento']);
$nombre = mysqli_real_escape_string($db, $_POST['nombre']);
$abreviatura = isset($_POST['abreviatura']) ? mysqli_real_escape_string($db, $_POST['abreviatura']) : null;
$usuario = isset($_POST['usuario']) ? mysqli_real_escape_string($db, $_POST['usuario']) : null;
$clave = isset($_POST['clave']) ? $_POST['clave'] : null;
$telefono = isset($_POST['telefono']) && !empty($_POST['telefono']) ? mysqli_real_escape_string($db, $_POST['telefono']) : null;
$email = isset($_POST['email']) && !empty($_POST['email']) ? mysqli_real_escape_string($db, $_POST['email']) : null;
$idEspecialidad = isset($_POST['idEspecialidad']) && !empty($_POST['idEspecialidad']) ? mysqli_real_escape_string($db, $_POST['idEspecialidad']) : null;
$observaciones = isset($_POST['observaciones']) ? mysqli_real_escape_string($db, $_POST['observaciones']) : null;
$prefRojas = isset($_POST['prefRojas']) ? $_POST['prefRojas'] : '';
$prefAmarillas = isset($_POST['prefAmarillas']) ? $_POST['prefAmarillas'] : '';

// Clave encriptada con MD5 (como en v3)
if (!empty($clave)) {
    $clave = md5($clave);
}

// Gestionar valores nulos
$telefono_sql = !empty($telefono) ? "'$telefono'" : "NULL";
$email_sql = !empty($email) ? "'$email'" : "NULL";
$idEspecialidad_sql = !empty($idEspecialidad) ? "'$idEspecialidad'" : "NULL";
$abreviatura_sql = !empty($abreviatura) ? "'$abreviatura'" : "NULL";
$usuario_sql = !empty($usuario) ? "'$usuario'" : "NULL";
$observaciones_sql = !empty($observaciones) ? "'$observaciones'" : "NULL";

// Si no llega un "id" de profesor, es una inserción de nuevo profesor
if (empty($_POST['id'])) {
    // Si no ha puesto clave le ponemos como clave su propio login
    if (empty($clave)) {
        $clave = md5($usuario);
    }
    
    // La columna "grupo" es NOT NULL sin valor por defecto; v3 no la pide, así que se guarda vacía
    $query = "INSERT INTO profesores (idDepartamento, nombre, abreviatura, usuario, clave, idEspecialidad, observaciones_horario, telefono, email, grupo) 
              VALUES ($idDepartamento, '$nombre', $abreviatura_sql, $usuario_sql, '$clave', $idEspecialidad_sql, $observaciones_sql, $telefono_sql, $email_sql, '')";
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al insertar el profesor: ' . mysqli_error($db)]);
        exit;
    }
    
    $id_nuevo = mysqli_insert_id($db);
    
    // Insertar preferencias horarias si existen
    if (!empty($prefRojas) || !empty($prefAmarillas)) {
        insertarPreferencias($db, $id_nuevo, $prefRojas, $prefAmarillas);
    }
    
    mysqli_close($db);
    echo json_encode(['success' => true, 'id' => $id_nuevo, 'mensaje' => 'Profesor creado correctamente']);
} else {
    // Actualizar profesor existente
    $idProfesor = intval($_POST['id']);
    
    // Distinguimos si hay que cambiar también la clave o se deja la que está
    if (empty($clave)) {
        $query = "UPDATE profesores SET nombre='$nombre', abreviatura=$abreviatura_sql, usuario=$usuario_sql, 
                  idEspecialidad=$idEspecialidad_sql, observaciones_horario=$observaciones_sql, 
                  telefono=$telefono_sql, email=$email_sql WHERE id=$idProfesor";
    } else {
        $query = "UPDATE profesores SET nombre='$nombre', abreviatura=$abreviatura_sql, usuario=$usuario_sql, 
                  clave='$clave', idEspecialidad=$idEspecialidad_sql, observaciones_horario=$observaciones_sql, 
                  telefono=$telefono_sql, email=$email_sql WHERE id=$idProfesor";
    }
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el profesor: ' . mysqli_error($db)]);
        exit;
    }
    
    // Actualizar preferencias horarias
    // Borramos viejas preferencias
    mysqli_query($db, "DELETE FROM preferencias_horario WHERE idProfesor = $idProfesor");
    // Añadimos nuevas preferencias
    if (!empty($prefRojas) || !empty($prefAmarillas)) {
        insertarPreferencias($db, $idProfesor, $prefRojas, $prefAmarillas);
    }
    
    mysqli_close($db);
    echo json_encode(['success' => true, 'mensaje' => 'Profesor actualizado correctamente']);
}

// Función auxiliar para insertar preferencias horarias
function insertarPreferencias($db, $idProfesor, $prefRojas, $prefAmarillas) {
    // Añadimos preferencias rojas (importantes)
    while (strlen($prefRojas) > 0) {
        $pref = substr($prefRojas, 0, 6);
        $dia = substr($pref, 0, 1);
        $hora = substr($pref, 1);
        $hora = str_replace('_', ':', $hora);
        $prefRojas = substr($prefRojas, 6);
        mysqli_query($db, "INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES ('$dia', '$hora', $idProfesor, 'R')");
    }
    // Añadimos preferencias amarillas (menos importantes)
    while (strlen($prefAmarillas) > 0) {
        $pref = substr($prefAmarillas, 0, 6);
        $dia = substr($pref, 0, 1);
        $hora = substr($pref, 1);
        $hora = str_replace('_', ':', $hora);
        $prefAmarillas = substr($prefAmarillas, 6);
        mysqli_query($db, "INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES ('$dia', '$hora', $idProfesor, 'A')");
    }
}
?>
