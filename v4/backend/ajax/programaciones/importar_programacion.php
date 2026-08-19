<?php

// Importa el contenido de una programación origen en una destino, borrando todo
// el contenido previo de esta programación destino

@session_start();

$permisos = !empty($_SESSION['idUsuario']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idMateriaOrigen']) && !empty($_REQUEST['idMateriaDestino']))
{
    include('../../includes/database.php');

    // Borramos contenidos previos de programación destino
    mysqli_query($db, "DELETE FROM contenidos_programaciones WHERE idMateria = " . $_REQUEST['idMateriaDestino']);
    mysqli_query($db, "DELETE FROM competencias_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = " . $_REQUEST['idMateriaDestino'] . ")");
    mysqli_query($db, "DELETE FROM criterios_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = " . $_REQUEST['idMateriaDestino'] . ")");
    mysqli_query($db, "DELETE FROM temas WHERE idMateria = " . $_REQUEST['idMateriaDestino']);

    // Insertamos cada contenido de la materia origen en la destino
    mysqli_query($db, "INSERT INTO contenidos_programaciones(idMateria, idApartado, texto) SELECT " . $_REQUEST['idMateriaDestino'] . " AS idMateria, idApartado, texto FROM contenidos_programaciones WHERE idMateria = " . $_REQUEST['idMateriaOrigen']);
    
    // Ahora falta relacionar el resto de tablas (temas, competencias, etc)

    // Temas
    mysqli_query($db, "INSERT INTO temas(idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto) SELECT " . $_REQUEST['idMateriaDestino'] . " AS idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto FROM temas WHERE idMateria = " . $_REQUEST['idMateriaOrigen']);
    // RA y CE
    $result = mysqli_query($db, "SELECT criterios_temas.codigo as CE, temas.orden as tema, resultados_aprendizaje.orden as RA FROM criterios_temas, temas, resultados_aprendizaje WHERE criterios_temas.idRA = resultados_aprendizaje.id AND criterios_temas.idTema = temas.id AND temas.idMateria = " . $_REQUEST['idMateriaOrigen']);
    while($fila = mysqli_fetch_assoc($result))
    {
        $codigoCE = $fila['CE'];
        $ordenRA = $fila['RA'];
        $numTema = $fila['tema'];
        // Insertamos los mismos datos para el tema y RA correspondiente a la materia destino
        // Buscamos el id del tema y del RA para esa materia
        $idRA = 0;
        $result2 = mysqli_query($db, "SELECT resultados_aprendizaje.id FROM resultados_aprendizaje WHERE idMateria = " . $_REQUEST['idMateriaDestino'] . " AND orden = $ordenRA");
        if($fila2 = mysqli_fetch_assoc($result2))
        {
            $idRA = $fila2['id'];
        }
        mysqli_free_result($result2);
        $result2 = mysqli_query($db, "SELECT temas.id FROM temas WHERE idMateria = " . $_REQUEST['idMateriaDestino'] . " AND orden=$numTema");
        if($fila2 = mysqli_fetch_assoc($result2))
        {
            $idTema = $fila2['id'];
        }
        mysqli_free_result($result2);
        if($idRA > 0 && $idTema > 0)
        {
            mysqli_query($db, "INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES ($idRA, '$codigoCE', $idTema)");
        }
    }
    
    include ('../../includes/database2.php');
}

?>