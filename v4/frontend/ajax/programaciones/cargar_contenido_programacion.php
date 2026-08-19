<?php

// Devuelve el texto asociado a una materia y apartado concreto de programación

$resultado = "";

if (!empty($_REQUEST['idApartado']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT texto FROM contenidos_programaciones WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idMateria=" . $_REQUEST['idMateria']);
    if (mysqli_num_rows($result) > 0)
        $resultado = mysqli_fetch_assoc($result)['texto'];
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $resultado;

?>