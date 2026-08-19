<?php

// Carga los datos de un profesor para mostrarlo junto a la selección de sus materias

if(!empty($_REQUEST['idProfesor']))
{
    include('../../includes/database.php');
    
    $idProfesor = $_REQUEST['idProfesor'];
    $resultado = mysqli_query($db, "SELECT * FROM profesores WHERE id = $idProfesor");

    $fila = mysqli_fetch_assoc($resultado);
    if ($fila['nombre'])
        $nombre = "Profesor/a: " . $fila['nombre'];
    else 
        $nombre = "";
    echo $nombre;
    
    mysqli_free_result($resultado);

    include ('../../includes/database2.php');
}

?>