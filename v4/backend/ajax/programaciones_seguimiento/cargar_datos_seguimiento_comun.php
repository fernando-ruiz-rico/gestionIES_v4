<?php
// Carga los datos de seguimiento comunes a todo el departamento del curso y evaluación indicados
// Devuelve los datos en formato JSON, o "error" si no se han encontrado

@session_start();

include('../../includes/database.php');
include('../../includes/utilidades.php');

if (!empty($_REQUEST['curso']) && !empty($_REQUEST['evaluacion']))
{   
    $curso = $_REQUEST['curso'];
    $evaluacion = $_REQUEST['evaluacion'];
    $modo = "";
    if (!empty($_REQUEST['modo']))
    {
        $modo = $_REQUEST['modo']; 
    }
    if ($modo == 'evaluacion')
        $evaluacion--;
    else if ($modo == 'curso')
        $curso = cursoAnterior();

    $result = mysqli_query($db, "SELECT funcionamiento_departamento, actividades_extraescolares, temporalizacion_defecto FROM seguimiento_programaciones_departamento WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND curso = '$curso' AND evaluacion = $evaluacion");
    if (mysqli_num_rows($result) == 0)
    {
        echo "error";
    } else {
        $resultado = mysqli_fetch_assoc($result);
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($resultado);
    }
    mysqli_free_result($result);    
}

include ('../../includes/database2.php');

?>