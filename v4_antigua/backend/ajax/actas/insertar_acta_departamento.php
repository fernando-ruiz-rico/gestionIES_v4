<?php

// Inserta/Actualiza un acta de departamento
// Devuelve "si" si se ha producido algún error, o el id del acta si todo ha ido bien

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
$error = TRUE;
$idActa = 0;

// Se necesita recibir la fecha del acta y el texto, junto con el departamento
if ($permisos && !empty($_REQUEST['fecha']) && !empty($_REQUEST['texto']) && !empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $fecha = DateTime::createFromFormat("d/m/Y", $_REQUEST['fecha'])->format('Y-m-d');
    $texto = $_REQUEST['texto'];
    $idDepartamento = $_REQUEST['idDepartamento'];

    // Si no llega un "id" de acta, es una inserción de nueva acta
    if (empty($_REQUEST['idActa']))
    {
        mysqli_query($db, "INSERT INTO actas_departamentos (idDepartamento, texto, fecha) VALUES ($idDepartamento, '$texto', '$fecha')");    
        $idActa = mysqli_insert_id($db);
    } else {
        mysqli_query($db, "UPDATE actas_departamentos SET texto='$texto', fecha='$fecha' WHERE id = " . $_REQUEST['idActa']);            
        $idActa = $_REQUEST['idActa'];
    }
    $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;    
    include ('../../includes/database2.php');
}
echo $error?'si':$idActa;

?>