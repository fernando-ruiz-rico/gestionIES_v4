<?php

// Inserta datos de seguimiento para la programación de una materia determinada

@session_start();
$error = TRUE;

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idMateria']) && !empty($_REQUEST['curso']) && !empty($_REQUEST['evaluacion']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT id FROM seguimiento_programaciones WHERE idMateria=" . $_REQUEST['idMateria'] . " AND curso = '" . $_REQUEST['curso'] . "' AND evaluacion = " . $_REQUEST['evaluacion']);
    if (mysqli_num_rows($result) > 0)
    {
        $fila = mysqli_fetch_assoc($result);
        $id = $fila['id'];
        $result2 = mysqli_query($db, "UPDATE seguimiento_programaciones SET temporalizacion = '" . addslashes($_REQUEST['temporalizacion']) . "', resultados = '" . addslashes($_REQUEST['resultados']) . "', resultados_porcentaje = " . $_REQUEST['resultadosPorcentaje'] . " WHERE id=$id");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO seguimiento_programaciones (idMateria, curso, evaluacion, temporalizacion, resultados, resultados_porcentaje) VALUES (" . $_REQUEST['idMateria'] . ", '" . $_REQUEST['curso'] . "', " . $_REQUEST['evaluacion'] . ", '" . addslashes($_REQUEST['temporalizacion']) . "', '" . addslashes($_REQUEST['resultados']) . "', " . $_REQUEST['resultadosPorcentaje'] . ")");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>