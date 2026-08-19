<?php

// Devuelve el texto asociado a un ciclo y apartado concreto de un PCCF

$resultado = "";

if (!empty($_REQUEST['idApartado']) && !empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT texto FROM contenidos_pccf WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idCiclo=" . $_REQUEST['idCiclo']);
    if (mysqli_num_rows($result) > 0)
        $resultado = mysqli_fetch_assoc($result)['texto'];
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $resultado;

?>