<?php

// Elimina los datos de la cualificación profesional
// No se puede borrar si tiene unidades de competencia asociadas
// Devuelve "si" si se ha producido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['codigo']))
{
    include ('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM cualificaciones_unidades WHERE codigoCualificacion = '" . $_REQUEST['codigo'] . "'");
    if (mysqli_num_rows($result) == 0)
    {
        mysqli_query($db, "DELETE FROM cualificaciones_profesionales WHERE codigo = '"  . $_REQUEST['codigo'] . "'");
        if (mysqli_affected_rows($db) > 0)
        {
            $error = FALSE;
        }
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>