<?php

// Inserta/Actualiza el contenido por defecto asociado a los temas de un departamento
// Devuelve "si" si se ha producido algún error

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

$error = TRUE;

if ($permisos && !empty($_REQUEST['idDepartamento']))
{
    $contexto = $_REQUEST['contexto'];
    $recursos = $_REQUEST['recursos'];
    $metodologia = $_REQUEST['metodologia'];
    $adaptaciones = $_REQUEST['adaptaciones'];

    include('../../includes/database.php');

    $result = mysqli_query($db, "SELECT * FROM contenidos_defecto_temas WHERE idDepartamento=" . $_REQUEST['idDepartamento']);
    if (mysqli_num_rows($result) > 0)
    {
        $result2 = mysqli_query($db, "UPDATE contenidos_defecto_temas SET contexto='$contexto', recursos='$recursos', metodologia='$metodologia', adaptaciones='$adaptaciones' WHERE idDepartamento=" . $_REQUEST['idDepartamento']);
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    } else {
        $result2 = mysqli_query($db, "INSERT INTO contenidos_defecto_temas (idDepartamento, contexto, recursos, metodologia, adaptaciones) VALUES (" . $_REQUEST['idDepartamento'] . ",'$contexto', '$recursos', '$metodologia', '$adaptaciones')");
        $error = mysqli_affected_rows($db) > 0? FALSE : TRUE;
    }
    mysqli_free_result($result);    

    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>
