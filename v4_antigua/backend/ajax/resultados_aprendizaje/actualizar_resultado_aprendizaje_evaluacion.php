<?php

// Inserta o actualiza el resultado de aprendizaje que se recibe

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idResultado']) && isset($_REQUEST['porcentajeEvaluacion']))
{
    include('../../includes/database.php');
    $idResultado = $_REQUEST['idResultado'];
    $porcentajeEvaluacion = $_REQUEST['porcentajeEvaluacion'];
    $esClave = isset($_REQUEST['esClave']) ? 1 : 0;
    mysqli_query($db, "UPDATE resultados_aprendizaje SET porcentaje_evaluacion = $porcentajeEvaluacion, es_clave = $esClave WHERE id = $idResultado");            
    include ('../../includes/database2.php');
}

?>