<?php

// Inserta o actualiza el ciclo que se recibe

@session_start();

$errorTema = FALSE;
$errorCriterios = FALSE;
$errorCompetencias = FALSE;

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idTema']))
{
    include('../../includes/database.php');
    $idTema = $_REQUEST['idTema'];
    $orden = $_REQUEST['orden'];
    $titulo = $_REQUEST['titulo'];
    $horas = $_REQUEST['horas'];
    $trimestre = $_REQUEST['trimestre'];
    $peso_evaluacion = $_REQUEST['peso_evaluacion'];
    $descripcion = $_REQUEST['descripcion'];
    $justificacion = $_REQUEST['justificacion'];
    $contexto = $_REQUEST['contexto'];
    $contenidos = $_REQUEST['contenidos'];
    $secuenciacion = $_REQUEST['secuenciacion'];
    $recursos = $_REQUEST['recursos'];
    $evaluacion = $_REQUEST['evaluacion'];
    $metodologia = $_REQUEST['metodologia'];
    $contexto_defecto = empty($_REQUEST['contexto_defecto'])?0:1;
    $recursos_defecto = empty($_REQUEST['recursos_defecto'])?0:1;
    $metodologia_defecto = empty($_REQUEST['metodologia_defecto'])?0:1;
    $adaptaciones_defecto = empty($_REQUEST['adaptaciones_defecto'])?0:1;

    mysqli_query($db, "UPDATE temas SET orden = $orden, titulo = '$titulo', horas = $horas, trimestre= $trimestre, peso_evaluacion = $peso_evaluacion, descripcion='$descripcion', justificacion='$justificacion', contexto='$contexto', contenidos='$contenidos', secuenciacion='$secuenciacion', recursos='$recursos', evaluacion='$evaluacion', metodologia='$metodologia', contexto_defecto=$contexto_defecto, recursos_defecto=$recursos_defecto, metodologia_defecto=$metodologia_defecto, adaptaciones_defecto=$adaptaciones_defecto WHERE id = " . $idTema);    
    if(mysqli_affected_rows($db) == 0)
        $errorTemas = TRUE;

    // Actualizamos los CE y las competencias
    mysqli_query($db, "DELETE FROM criterios_temas WHERE idTema = $idTema");
    mysqli_query($db, "DELETE FROM competencias_temas WHERE idTema = $idTema");
    if(isset($_REQUEST['ce']))
    {
        $criterios = $_REQUEST['ce'];
        foreach($criterios as $valor)
        {
            $partes = explode('_', $valor);
            $idRA = $partes[1];
            $codigo = $partes[2];
            mysqli_query($db, "INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES ($idRA, '$codigo', $idTema)");
            if(mysqli_affected_rows($db) == 0)
                $errorCriterios = TRUE;
        }
    }
    if(isset($_REQUEST['com']))
    {
        $competencias = $_REQUEST['com'];
        foreach($competencias as $valor)
        {
            mysqli_query($db, "INSERT INTO competencias_temas (idCompetencia, idTema) VALUES ($valor, $idTema)");
            if(mysqli_affected_rows($db) == 0)
                $errorCompetencias = TRUE;
        }
    }
    include ('../../includes/database2.php');
}

echo json_encode(array("errorTema" => $errorTema, "errorCriterios" => $errorCriterios, "errorCompetencias" => $errorCompetencias));

?>