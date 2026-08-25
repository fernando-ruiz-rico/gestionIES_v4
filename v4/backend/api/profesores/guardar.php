<?php
// API endpoint para insertar o actualizar un profesor
// Requiere sesión iniciada y rol de admin (o el propio profesor si es su perfil)
// Recibe (JSON): nombre, idDepartamento, idEspecialidad (requeridos), abreviatura,
//                usuario, clave, telefono, email, observaciones, prefRojas, prefAmarillas
// Devuelve: success (true/false), mensaje

require_once '../../config.php';
cabeceraJson();

$session = checkSession();
$datos = cuerpoJson();

// Verificar permisos: un admin, o el propio profesor editando su perfil
$permisos = ($_SESSION['rol'] == ROLE_ADMIN) ||
            (!empty($session['idUsuario']) && !empty($datos['id']) && $session['idUsuario'] == $datos['id']);

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

// Validar datos requeridos
if (empty($datos['nombre']) || empty($datos['idDepartamento'])) {
    sendJSONError('Nombre y departamento son requeridos', 400);
}

// Las consultas están parametrizadas, así que ya no hace falta escapar los datos
$idDepartamento = datosOptimoInt($datos, 'idDepartamento');
$nombre = $datos['nombre'];

// Campos opcionales: datosOptimo devuelve el valor si llega no vacío, y null si no
$abreviatura = datosOptimo($datos, 'abreviatura');
$usuario = datosOptimo($datos, 'usuario');
$clave = datosOptimo($datos, 'clave');
$telefono = datosOptimo($datos, 'telefono');
$email = datosOptimo($datos, 'email');
$idEspecialidad = datosOptimo($datos, 'idEspecialidad');
$observaciones = datosOptimo($datos, 'observaciones');
$prefRojas = datosOptimo($datos, 'prefRojas');
$prefAmarillas = datosOptimo($datos, 'prefAmarillas');

// Clave encriptada con MD5 (como en v3)
if (!empty($clave)) {
    $clave = md5($clave);
}

// Si no llega un "id" de profesor, es una inserción de nuevo profesor
try {
    $db = Db::open();

    if (empty($datos['id'])) {
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
        $idProfesor = datosOptimoInt($datos, 'id');

        // Distinguimos si hay que cambiar también la clave o se deja la que está:
        // la columna "clave" solo se actualiza si ha llegado una nueva.
        $campos = array('nombre', 'abreviatura', 'usuario', 'idEspecialidad', 'observaciones_horario', 'telefono', 'email');
        $valores = array($nombre, $abreviatura, $usuario, $idEspecialidad, $observaciones, $telefono, $email);
        if (!empty($clave)) {
            $campos[] = 'clave';
            $valores[] = $clave;
        }
        $asignaciones = array();
        for ($i = 0; $i < count($campos); $i++) {
            $asignaciones[] = $campos[$i] . ' = ?';
        }
        $sql = "UPDATE profesores SET " . implode(', ', $asignaciones) . " WHERE id = ?";
        $parametros = array_merge($valores, array($idProfesor));
        $db->execute($sql, ...$parametros);

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
    $error = empty($datos['id']) ? 'Error al insertar el profesor' : 'Error al actualizar el profesor';
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
