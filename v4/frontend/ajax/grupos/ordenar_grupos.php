<?php

// Reordena los grupos que recibe entre sí

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['orden']))
{
    include('../../includes/database.php');
    // Los grupos vienen en un parámetro textual, cada uno con el prefijo "gr" seguido del código del grupo
    // Aquí se separan las partes y se le asigna a cada uno un orden correlativo
    $orden = $_REQUEST['orden'];
    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++)
    {
        $codGrupo = substr($partes[$i-1], 2);
        mysqli_query($db, "UPDATE grupos SET orden=$i WHERE id=$codGrupo");
    }
    include ('../../includes/database2.php');
}

?>