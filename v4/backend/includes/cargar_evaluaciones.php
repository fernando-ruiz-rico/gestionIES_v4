<?php
@session_start();

if (isset($_SESSION['idUsuario'])) 
{
    $evaluaciones = consultarBaseDeDatos("SELECT * FROM evaluaciones");
    foreach($evaluaciones as $e) {
        echo '<option value="'.$e['id'].'">'.$e['nombre'].'</option>';
    }
}
?>