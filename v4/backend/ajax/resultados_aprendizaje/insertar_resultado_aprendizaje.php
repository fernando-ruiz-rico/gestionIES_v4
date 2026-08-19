<?php

// Inserta o actualiza el resultado de aprendizaje que se recibe

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['texto']) && !empty($_REQUEST['orden']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    $texto = $_REQUEST['texto'];
    $orden = $_REQUEST['orden'];
    $idMateria = $_REQUEST['idMateria'];
    $porcentajeEmpresa = $_REQUEST['porcentajeEmpresa'];

    // Si no llega id de resultado es una inserción
    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO resultados_aprendizaje (idMateria, texto, orden, porcentaje_empresa) VALUES ($idMateria, '$texto', $orden, $porcentajeEmpresa)");    
    } else {
        mysqli_query($db, "UPDATE resultados_aprendizaje SET texto='$texto', orden=$orden, porcentaje_empresa = $porcentajeEmpresa WHERE id = " . $_REQUEST['id']);            
    }
    include ('../../includes/database2.php');
}

?>