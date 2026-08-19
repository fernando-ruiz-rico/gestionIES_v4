<?php

// Inserta o modifica el contenido específico de una materia y apartado especificados
// Devuelve "si" si ha habido algún error al guardar los datos
// IMPORTANTE: si no hay cambios respecto al último valor almacenado, devolverá error

@session_start();
$error = TRUE;

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idApartado']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM contenidos_programaciones WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idMateria=" . $_REQUEST['idMateria']);
    if (mysqli_num_rows($result) > 0)
    {
        $result2 = mysqli_query($db, "UPDATE contenidos_programaciones SET texto = '" . addslashes($_REQUEST['texto']) . "' WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idMateria=" . $_REQUEST['idMateria']);
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO contenidos_programaciones (idApartado, idMateria, texto) VALUES (" . $_REQUEST['idApartado'] . "," . $_REQUEST['idMateria'] . ",'" . addslashes($_REQUEST['texto']) . "')");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>