<?php

// Inserta/modifica los datos de un profesor en la base de datos.
// Devuelve "si" si ha habido algún error en la inserción

@session_start();
$permisos = (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') || 
            (!empty($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $_REQUEST['id']);
$result = FALSE;
$resultError = "";

// Se necesita recibir al menos el nombre del profesor y su especialidad
// Internamente también se habrá rellenado en un campo oculto el departamento asociado
if ($permisos && !empty($_REQUEST['nombre']) && !empty($_REQUEST['idEspecialidad']))
{
    include('../../includes/database.php');
    $idDepartamento = $_REQUEST['idDepartamento'];
    $nombre = addslashes($_REQUEST['nombre']);
    $abreviatura = $_REQUEST['abreviatura'];
    $usuario = $_REQUEST['usuario'];
    $clave = $_REQUEST['clave'];
    $telefono = $_REQUEST['telefono'];
    $email = $_REQUEST['email'];
    $idEspecialidad = $_REQUEST['idEspecialidad'];
    $observaciones = addslashes($_REQUEST['observaciones']);
    $prefRojas = $_REQUEST['prefRojas'];
    $prefAmarillas = $_REQUEST['prefAmarillas'];
        
    // Clave encriptada
    if (!empty($clave))
        $clave = md5($clave);

    // Gestionamos valores que pueden ser nulos

    if (!empty($telefono))
        $telefono = "'$telefono'";
    else
        $telefono = "NULL";

    if (!empty($email))
        $email = "'$email'";
    else
        $email = "NULL";

    if (!empty($idEspecialidad))
        $idEspecialidad = "'$idEspecialidad'";
    else
        $idEspecialidad = "NULL";

    // Si no llega un "id" de profesor, es una inserción de nuevo profesor
    if (empty($_REQUEST['id']))    
    {
        // Si no ha puesto clave le ponemos como clave su propio login
        if (empty($clave))
            $clave = md5($usuario);

        $result = mysqli_query($db, "INSERT INTO profesores (idDepartamento, nombre, abreviatura, usuario, clave, idEspecialidad, observaciones_horario, telefono, email) VALUES ($idDepartamento, '$nombre', '$abreviatura', '$usuario', '$clave', $idEspecialidad, '$observaciones', $telefono, $email)");
        
        if (!$result)
        {
            $resultError = mysqli_error($db);
        }
    }
    // Si llega un "id", es una actualización de profesor
    else
    {
        $idProfesor = $_REQUEST['id'];
        // Distinguimos si hay que cambiar también la clave o se deja la que está
        if (empty($clave))
            $result = mysqli_query($db, "UPDATE profesores SET nombre = '$nombre', abreviatura = '$abreviatura', usuario = '$usuario', idEspecialidad=$idEspecialidad, observaciones_horario='$observaciones', telefono=$telefono, email=$email WHERE id=" . $idProfesor);
        else
            $result = mysqli_query($db, "UPDATE profesores SET nombre = '$nombre', abreviatura = '$abreviatura', usuario = '$usuario', clave = '$clave', idEspecialidad=$idEspecialidad, observaciones_horario='$observaciones', telefono=$telefono, email=$email WHERE id=" . $idProfesor);
        if (!$result)
            $resultError = mysqli_error($db);
    }
    
    // En el caso de modificación, se pueden cambiar también sus preferencias horarias
    if (!empty($idProfesor))
    {
        // Borramos viejas preferencias horarias
        mysqli_query($db, "DELETE FROM preferencias_horario WHERE idProfesor = " . $idProfesor);
        // Añadimos preferencias rojas (importantes)
        while (strlen($prefRojas) > 0)
        {
            $pref = substr($prefRojas, 0, 6);
            $dia = substr($pref, 0, 1);
            $hora = substr($pref, 1);
            $hora = str_replace('_', ':', $hora);
            $prefRojas = substr($prefRojas, 6);
            mysqli_query($db, "INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES ('$dia', '$hora', $idProfesor, 'R')");
        }
        // Añadimos preferencias amarillas (menos importantes)
        while (strlen($prefAmarillas) > 0)
        {
            $pref = substr($prefAmarillas, 0, 6);
            $dia = substr($pref, 0, 1);
            $hora = substr($pref, 1);
            $hora = str_replace('_', ':', $hora);
            $prefAmarillas = substr($prefAmarillas, 6);
            mysqli_query($db, "INSERT INTO preferencias_horario (dia, hora, idProfesor, preferencia) VALUES ('$dia', '$hora', $idProfesor, 'A')");
        }    
    }
    include ('../../includes/database2.php');
}

if ($result)
    echo "no";
else
    echo "si" . $resultError;

?>