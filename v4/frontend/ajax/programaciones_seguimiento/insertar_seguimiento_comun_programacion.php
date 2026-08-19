<?php

// Inserta contenido del seguimiento de las programaciones común para todos los módulos de un departamento

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

$error = TRUE;

if ($permisos && !empty($_REQUEST['curso']) && !empty($_REQUEST['evaluacion']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT id FROM seguimiento_programaciones_departamento WHERE idDepartamento=" . $_SESSION['departamentoUsuario'] . " AND curso = '" . $_REQUEST['curso'] . "' AND evaluacion = " . $_REQUEST['evaluacion']);
    if (mysqli_num_rows($result) > 0)
    {
        $fila = mysqli_fetch_assoc($result);
        $id = $fila['id'];
        $result2 = mysqli_query($db, "UPDATE seguimiento_programaciones_departamento SET funcionamiento_departamento = '" . addslashes($_REQUEST['funcionamiento_departamento']) . "', actividades_extraescolares = '" . addslashes($_REQUEST['actividades']) . "', temporalizacion_defecto = '" . addslashes($_REQUEST['temporalizacion_defecto']) . "' WHERE id=$id");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO seguimiento_programaciones_departamento (idDepartamento, curso, evaluacion, funcionamiento_departamento, actividades_extraescolares, temporalizacion_defecto) VALUES (" . $_SESSION['departamentoUsuario'] . ", '" . $_REQUEST['curso'] . "', " . $_REQUEST['evaluacion'] . ", '" . addslashes($_REQUEST['funcionamiento_departamento']) . "', '" . addslashes($_REQUEST['actividades']) . "', '" . addslashes($_REQUEST['temporalizacion_defecto']) . "')");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>