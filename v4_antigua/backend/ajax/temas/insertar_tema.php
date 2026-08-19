<?php
@session_start();

// Inserta una nueva unidad de programación (tema) en la base de datos
if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idMateria']) && !empty($_REQUEST['orden']) && !empty($_REQUEST['titulo'])) {
    include('../../includes/utilidades.php');
    include('../../includes/database.php');

    $idMateria = (int)$_REQUEST['idMateria'];
    $orden     = (int)$_REQUEST['orden'];
    $titulo    = $_REQUEST['titulo'];

    // Campos de texto: usan '' por defecto
    $descripcion          = getReqStr('descripcion');
    $justificacion        = getReqStr('justificacion');
    $contexto             = getReqStr('contexto');
    $contenidos           = getReqStr('contenidos');
    $secuenciacion        = getReqStr('secuenciacion');
    $recursos             = getReqStr('recursos');
    $evaluacion           = getReqStr('evaluacion');
    $metodologia          = getReqStr('metodologia');
    $adaptaciones         = getReqStr('adaptaciones');

    // Campos numéricos con valor por defecto 0
    $horas                = getReqInt('horas');
    $trimestre            = getReqInt('trimestre');
    $peso_evaluacion      = getReqInt('peso_evaluacion');

    // Flags (tinyint) con valor por defecto 1
    $contexto_defecto     = getReqInt('contexto_defecto', 1);
    $recursos_defecto     = getReqInt('recursos_defecto', 1);
    $metodologia_defecto  = getReqInt('metodologia_defecto', 1);
    $adaptaciones_defecto = getReqInt('adaptaciones_defecto', 1);

    // Inserción directa en la base de datos
    $sql = "INSERT INTO temas (
        idMateria, orden, titulo, horas, trimestre, peso_evaluacion,
        descripcion, justificacion, contexto, contenidos, secuenciacion,
        recursos, evaluacion, metodologia, adaptaciones,
        contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto
    ) VALUES (
        $idMateria, $orden, '$titulo', $horas, $trimestre, $peso_evaluacion,
        '$descripcion', '$justificacion', '$contexto', '$contenidos', '$secuenciacion',
        '$recursos', '$evaluacion', '$metodologia', '$adaptaciones',
        $contexto_defecto, $recursos_defecto, $metodologia_defecto, $adaptaciones_defecto
    )";

    mysqli_query($db, $sql);

    include('../../includes/database2.php');
}
?>