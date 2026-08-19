<?php

// Devuelve un listado de opciones HTML con el id y la fecha de cada acta
// del departamento indicado en sesión, ordenadas de más reciente a más antigua

@session_start();

if (isset($_SESSION['departamentoUsuario']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT id, fecha FROM actas_departamentos WHERE idDepartamento=" . $_SESSION['departamentoUsuario'] . ' ORDER BY fecha DESC');
    echo '<option value="">--Selecciona una fecha--</option>';
    while ($fila = mysqli_fetch_assoc($result))
    {
        echo '<option value="' . $fila['id'] . '">' . date('d/m/Y', strtotime($fila['fecha'])) . '</option>';
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>