<?php

// Reordena las competencias entre sí

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['orden']))
{
    include('../../includes/database.php');
    // Las competencias vienen en un parámetro textual, cada una con el prefijo "cm" seguido del id de la competencia
    // Aquí se separan las partes y se le asigna a cada una un orden correlativo
    $orden = $_REQUEST['orden'];
    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++)
    {
        $codComp = substr($partes[$i-1], 2);
        mysqli_query($db, "UPDATE competencias_ciclos SET orden=$i WHERE id=$codComp");
    }
    include ('../../includes/database2.php');
}

?>