<?php

// Inserta/Modifica una competencia de un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['texto']) && !empty($_REQUEST['codigo']))
{
    include('../../includes/database.php');
    $idCiclo = $_REQUEST['idCiclo'];
    $codigo = $_REQUEST['codigo'];
    $texto = $_REQUEST['texto'];
    $tipo = $_REQUEST['tipo'];

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO competencias_ciclos (codigo, texto, tipo, idCiclo) VALUES ('$codigo', '$texto', $tipo, $idCiclo)");    
    } else {
        mysqli_query($db, "UPDATE competencias_ciclos SET codigo='$codigo', texto='$texto', tipo=$tipo WHERE id = " . $_REQUEST['id']);            
    }    
    include ('../../includes/database2.php');
}

?>