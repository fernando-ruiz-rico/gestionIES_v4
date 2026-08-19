<?php

// Devuelve el texto asociado a una grupo, tema y profesor concretos de programación de aula

$resultado = "";

if (isset($_REQUEST['idTema']) && !empty($_REQUEST['idGrupo']) && !empty($_REQUEST['idProfesor']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT texto FROM programaciones_aula_temas WHERE idTema=" . $_REQUEST['idTema'] . " AND idGrupo=" . $_REQUEST['idGrupo'] . " AND idProfesor=" . $_REQUEST['idProfesor']);
    if (mysqli_num_rows($result) > 0)
        $resultado = mysqli_fetch_assoc($result)['texto'];
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $resultado;

?>