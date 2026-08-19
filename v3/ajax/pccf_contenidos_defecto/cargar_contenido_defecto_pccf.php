<?php

// Carga el contenido (texto) del apartado de PCCF indicado, para el departamento indicado

$resultado = "";

if (!empty($_REQUEST['idApartado']) && !empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT texto FROM contenidos_defecto_pccf WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idDepartamento=" . $_REQUEST['idDepartamento']);
    if (mysqli_num_rows($result) > 0)
        $resultado = mysqli_fetch_assoc($result)['texto'];
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $resultado;

?>