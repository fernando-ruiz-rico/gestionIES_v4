<?php

// Establece el orden entre los cursos

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['orden']))
{
    include('../../includes/database.php');
    // Llegan los códigos de los cursos separados por comas:
    // cu1,cu11,cu23,cu2...
    $orden = $_REQUEST['orden'];
    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++)
    {
        // Dentro de cada parte, el código del curso está tras el prefijo "cu"
        $codCurso = substr($partes[$i-1], 2);
        mysqli_query($db, "UPDATE cursos SET orden=$i WHERE id=$codCurso");
    }
    include ('../../includes/database2.php');
}

?>