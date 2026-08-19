<?php

// Inserta o modifica el contenido específico de un tema, grupo y profesor en la programación de aula
// Devuelve "si" si ha habido algún error al guardar los datos
// IMPORTANTE: si no hay cambios respecto al último valor almacenado, devolverá error

@session_start();
$error = TRUE;

if (!empty($_SESSION['idUsuario']) && isset($_REQUEST['idTema']) && !empty($_REQUEST['idGrupo']) && !empty($_REQUEST['idProfesor']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM programaciones_aula_temas WHERE idTema=" . $_REQUEST['idTema'] . " AND idGrupo=" . $_REQUEST['idGrupo'] . " AND idProfesor=" . $_REQUEST['idProfesor']);
    if (mysqli_num_rows($result) > 0)
    {
        $result2 = mysqli_query($db, "UPDATE programaciones_aula_temas SET texto = '" . addslashes($_REQUEST['texto']) . "' WHERE idTema=" . $_REQUEST['idTema'] . " AND idGrupo=" . $_REQUEST['idGrupo'] . " AND idProfesor=" . $_REQUEST['idProfesor']);
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO programaciones_aula_temas (idTema, idGrupo, idProfesor, texto) VALUES (" . $_REQUEST['idTema'] . "," . $_REQUEST['idGrupo'] . "," . $_REQUEST['idProfesor'] . ",'" . addslashes($_REQUEST['texto']) . "')");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>