<?php

// Devuelve en formato JSON los datos de los "checkboxes" marcados para un tema
// en cuanto a criterios de evaluación y competencias

if (!empty($_REQUEST['idTema']))
{
    include('../../includes/database.php');
    // Criterios de evaluación
    $result = mysqli_query($db, "SELECT * FROM criterios_temas WHERE idTema=" . $_REQUEST['idTema']);
    $criterios = array();
    while($fila = mysqli_fetch_assoc($result))
    {
        $idRA = $fila['idRA'];
        $codigo = $fila['codigo'];
        $criterios[] = "ce_" . $idRA . "_" . $codigo;
    }
    mysqli_free_result($result);    

    // Competencias
    $result = mysqli_query($db, "SELECT * FROM competencias_temas WHERE idTema=" . $_REQUEST['idTema']);
    $competencias = array();
    while($fila = mysqli_fetch_assoc($result))
    {
        $idCompetencia = $fila['idCompetencia'];
        $competencias[] = "com" . $idCompetencia;
    }
    mysqli_free_result($result);    
    $resultado = array("criterios" => $criterios, "competencias" => $competencias);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    include ('../../includes/database2.php');
}

?>