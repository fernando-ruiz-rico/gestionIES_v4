<?php

// Inserta o modifica el contenido específico de un ciclo y apartado especificados en el PCCF
// Devuelve "si" si ha habido algún error al guardar los datos
// IMPORTANTE: si no hay cambios respecto al último valor almacenado, devolverá error

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
$error = TRUE;

if ($permisos && !empty($_REQUEST['idApartado']) && !empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM contenidos_pccf WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idCiclo=" . $_REQUEST['idCiclo']);
    if (mysqli_num_rows($result) > 0)
    {
        $result2 = mysqli_query($db, "UPDATE contenidos_pccf SET texto = '" . addslashes($_REQUEST['texto']) . "' WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idCiclo=" . $_REQUEST['idCiclo']);
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO contenidos_pccf (idApartado, idCiclo, texto) VALUES (" . $_REQUEST['idApartado'] . "," . $_REQUEST['idCiclo'] . ",'" . addslashes($_REQUEST['texto']) . "')");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>