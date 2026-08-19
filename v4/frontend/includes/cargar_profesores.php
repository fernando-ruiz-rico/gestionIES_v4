<?php

// Sólo el admin o el jefe de departamento pueden cargar la lista completa de profesores
if (isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin')) 
{
    include('includes/database.php');

    $resultados = consultarBaseDeDatos("SELECT * FROM profesores ORDER BY nombre");
    foreach ($resultados as $fila)
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        if(isset($_REQUEST['idProfesor']) && $_REQUEST['idProfesor'] == $id)
        {
            echo '<option value="' . $id . '" selected>' . $nombre . '</option>';
        }
        else
        {
            echo '<option value="' . $id . '">' . $nombre . '</option>';
        }
    }

    include('includes/database2.php');
}

?>        