<?php

// Reordena los apartados según el nuevo orden recibido

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['orden']))
{
    // Se recibe un parámetro "orden" que es un string con los códigos de los 
    // apartados, en el orden en que se quieren guardar. Cada código viene precedido por un prefijo
    // "ap", que se elimina después: ap1,ap12,ap8,...
    include('../../includes/database.php');
    $orden = $_REQUEST['orden'];
    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++)
    {
        // Eliminamos el prefijo "ap" del apartado actual
        $codApartado = substr($partes[$i-1], 2);
        mysqli_query($db, "UPDATE apartados_pccf SET orden=$i WHERE id=$codApartado");
    }
    include ('../includes/database2.php');
}

?>