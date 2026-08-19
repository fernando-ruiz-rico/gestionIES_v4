<?php

// Inserta/Modifica un departamento con los datos que recibe por POST

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

// Al menos se debe especificar un nombre de escenario y departamento(s) asociado(s)
if ($permisos && !empty($_REQUEST['nombre']) && !empty($_REQUEST['departamentoEscenario']))
{
    include('../../includes/database.php');

    $nombre = $_REQUEST['nombre'];
    
    // Si no llega "id" de escenario es una inserción de nuevo escenario
    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO escenarios_desideratas (nombre) VALUES ('$nombre')");
        if (mysqli_affected_rows($db) > 0)
        {
            // Nos quedamos con el "id" del nuevo escenario para asignarle los departamentos
            $result = mysqli_query($db, "SELECT MAX(id) AS idMax FROM escenarios_desideratas");
            $fila = mysqli_fetch_assoc($result);
            $idMax = $fila['idMax'];
            mysqli_free_result($result);
            foreach ($_REQUEST['departamentoEscenario'] AS $dep)
            {
                mysqli_query($db, "INSERT INTO departamentos_escenarios (idEscenario, idDepartamento) VALUES ($idMax, $dep)");
            }
        }
    // Si llega "id" es una actualización
    } else {
        // Borramos departamentos previamente asociados al escenario
        mysqli_query($db, "DELETE FROM departamentos_escenarios WHERE idEscenario = " . $_REQUEST['id']);
        // Cambiamos el nombre del escenario si procede
        mysqli_query($db, "UPDATE escenarios_desideratas SET nombre='$nombre' WHERE id = " . $_REQUEST['id']);            
        // Asignamos los departamentos elegidos en esta actualización
        foreach ($_REQUEST['departamentoEscenario'] AS $dep)
        {
            mysqli_query($db, "INSERT INTO departamentos_escenarios (idEscenario, idDepartamento) VALUES (" . $_REQUEST['id'] . ", $dep)");
        }
    }
    include ('../../includes/database2.php');
}

?>