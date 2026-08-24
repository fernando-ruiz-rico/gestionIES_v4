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
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

// Validar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['idDepartamento'])) {
    sendJSONError('Nombre y departamento son requeridos', 400);
}

// Las consultas están parametrizadas, así que ya no hace falta escapar los datos
$idDepartamento = intval($_POST['idDepartamento']);
$nombre = $_POST['nombre'];
$abreviatura = isset($_POST['abreviatura']) ? $_POST['abreviatura'] : null;
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : null;
$clave = isset($_POST['clave']) ? $_POST['clave'] : null;
$telefono = isset($_POST['telefono']) && !empty($_POST['telefono']) ? $_POST['telefono'] : null;
$email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : null;
$idEspecialidad = isset($_POST['idEspecialidad']) && !empty($_POST['idEspecialidad']) ? $_POST['idEspecialidad'] : null;
$observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : null;
$prefRojas = isset($_POST['prefRojas']) ? $_POST['prefRojas'] : '';
$prefAmarillas = isset($_POST['prefAmarillas']) ? $_POST['prefAmarillas'] : '';

// Clave encriptada con MD5 (como en v3)
if (!empty($clave)) {
    $clave = md5($clave);
}

// Gestionar valores nulos
$telefono = !empty($telefono) ? $telefono : null;
$email = !empty($email) ? $email : null;
$idEspecialidad = !empty($idEspecialidad) ? $idEspecialidad : null;
$abreviatura = !empty($abreviatura) ? $abreviatura : null;
$usuario = !empty($usuario) ? $usuario : null;
$observaciones = !empty($observaciones) ? $observaciones : null;

// Si no llega un "id" de profesor, es una inserción de nuevo profesor
try {
    $db = Db::open();

    if (empty($_POST['id'])) {
        // Si no ha puesto clave le ponemos como clave su propio login
        if (empty($clave)) {
            $clave = md5($usuario);
        }

        // La columna "grupo" es NOT NULL sin valor por defecto; v3 no la pide, así que se guarda vacía
        $db->execute("INSERT INTO profesores (idDepartamento, nombre, abreviatura, usuario, clave, idEspecialidad, observaciones_horario, telefono, email, grupo)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '')",
            $idDepartamento, $nombre, $abreviatura, $usuario, $clave, $idEspecialidad, $observaciones, $telefono, $email);

        $id_nuevo = $db->insertId();

        // Insertar preferencias horarias si existen
        if (!empty($prefRojas) || !empty($prefAmarillas)) {
            insertarPreferencias($db, $id_nuevo, $prefRojas, $prefAmarillas);
        }

        sendJSONSuccess(array('id' => $id_nuevo, 'mensaje' => 'Profesor creado correctamente'));
    } else {
        // Actualizar profesor existente
        $idProfesor = intval($_POST['id']);

        // Distinguimos si hay que cambiar también la clave o se deja la que está
        if (empty($clave)) {
            $db->execute("UPDATE profesores SET nombre=?, abreviatura=?, usuario=?,
                          idEspecialidad=?, observaciones_horario=?,
                          telefono=?, email=? WHERE id=?",
                $nombre, $abreviatura, $usuario, $idEspecialidad, $observaciones, $telefono, $email, $idProfesor);
        } else {
            $db->execute("UPDATE profesores SET nombre=?, abreviatura=?, usuario=?,
                          clave=?, idEspecialidad=?, observaciones_horario=?,
                          telefono=?, email=? WHERE id=?",
                $nombre, $abreviatura, $usuario, $clave, $idEspecialidad, $observaciones, $telefono, $email, $idProfesor);
        }

        // Actualizar preferencias horarias
        // Borramos viejas preferencias
        $db->execute("DELETE FROM preferencias_horario WHERE idProfesor = ?", $idProfesor);
        // Añadimos nuevas preferencias
        if (!empty($prefRojas) || !empty($prefAmarillas)) {
            insertarPreferencias($db, $idProfesor, $prefRojas, $prefAmarillas);
        }

        sendJSONSuccess(array('mensaje' => 'Profesor actualizado correctamente'));
    }
} catch (DbException $e) {
    $error = empty($_POST['id']) ? 'Error al insertar el profesor' : 'Error al actualizar el profesor';
    sendJSONError($error . ': ' . $e->getMessage(), 500);
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
        $db->execute("INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES (?, ?, ?, 'R')", $dia, $hora, $idProfesor);
    }
    // Añadimos preferencias amarillas (menos importantes)
    while (strlen($prefAmarillas) > 0) {
        $pref = substr($prefAmarillas, 0, 6);
        $dia = substr($prefAmarillas, 0, 1);
        $hora = substr($pref, 1);
        $hora = str_replace('_', ':', $hora);
        $prefAmarillas = substr($prefAmarillas, 6);
        $db->execute("INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES (?, ?, ?, 'A')", $dia, $hora, $idProfesor);
    }
}
?>
