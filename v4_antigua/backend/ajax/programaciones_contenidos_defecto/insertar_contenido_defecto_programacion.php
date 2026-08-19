<?php

// Inserta/Actualiza el contenido que se envía para el apartado indicado
// Devuelve "si" si se ha producido algún error

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

$error = TRUE;

if ($permisos && !empty($_REQUEST['idApartado']) && !empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');

    // Si no llega ningún texto, borramos el contenido por defecto en cuestión
    $texto = trim(isset($_REQUEST['texto']) ? $_REQUEST['texto'] : '');
    if(empty($texto))
    {
        mysqli_query($db, "DELETE FROM contenidos_defecto_programaciones WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idDepartamento=" . $_REQUEST['idDepartamento']);
        $error = FALSE;
    }
    // En caso contrario, se trata de una inserción o una modificación, dependiendo de si ya existe contenido para ese apartado
    else
    {
        $result = mysqli_query($db, "SELECT * FROM contenidos_defecto_programaciones WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idDepartamento=" . $_REQUEST['idDepartamento']);
        if (mysqli_num_rows($result) > 0)
        {
            $result2 = mysqli_query($db, "UPDATE contenidos_defecto_programaciones SET texto = '" . addslashes($_REQUEST['texto']) . "' WHERE idApartado=" . $_REQUEST['idApartado'] . " AND idDepartamento=" . $_REQUEST['idDepartamento']);
            $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
        } else {
            $result2 = mysqli_query($db, "INSERT INTO contenidos_defecto_programaciones (idApartado, idDepartamento, texto) VALUES (" . $_REQUEST['idApartado'] . "," . $_REQUEST['idDepartamento'] . ",'" . addslashes($_REQUEST['texto']) . "')");
            $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
        }
        mysqli_free_result($result);    
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>
