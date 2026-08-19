<?php

// Ordena los profesores que recibe (de un departamento) para establecer el orden de elección de materias

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['orden']))
{
    include('../../includes/database.php');
    // Lo que se recibe en el parámetro "orden" son los id de los profesores en el orden en que
    // se quieren asignar. Para ello, cada profesor en el listado viene en una caja con id igual a
    // "pr" seguido de su código de profesor: se le quita el prefijo "pr" a cada uno para obtener
    // el código.
    $orden = $_REQUEST['orden'];
    $partes = explode(",", $orden);
    for ($i = 1; $i <= count($partes); $i++)
    {
        $codProfesor = substr($partes[$i-1], 2);
        mysqli_query($db, "UPDATE profesores SET orden=$i WHERE id=$codProfesor");
    }
    include ('../../includes/database2.php');
}

?>