<?php

// Carga los datos de seguimiento del curso, materia y evaluación indicados
// Devuelve los datos en formato JSON, o "error" si no se han encontrado

if (!empty($_REQUEST['idMateria']) && !empty($_REQUEST['curso']) && !empty($_REQUEST['evaluacion']))
{   
    include('../../includes/database.php');
    include('../../includes/utilidades.php');
    $idMateria = $_REQUEST['idMateria'];
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

    $result = mysqli_query($db, "SELECT temporalizacion, resultados, resultados_porcentaje FROM seguimiento_programaciones WHERE idMateria = $idMateria AND curso = '$curso' AND evaluacion = $evaluacion");
    if (mysqli_num_rows($result) == 0)
    {
        echo "error";
    } else {
        $resultado = mysqli_fetch_assoc($result);
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($resultado);
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}
?>