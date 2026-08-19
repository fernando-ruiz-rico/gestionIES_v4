<?php

// Devuelve un JSON con los datos de los apartados para la materia indicada

$resultado = array();

if (!empty($_REQUEST['idMateria']))
{
    $idMateria = $_REQUEST['idMateria'];

    include('../../includes/database.php');

    // Primero vemos si es una materia de ciclos o no
    $result = mysqli_query($db, "SELECT ciclos.id AS id FROM ciclos, cursos, cursos_ciclos, materias WHERE ciclos.id = cursos_ciclos.idCiclo AND cursos.id = cursos_ciclos.idCurso AND materias.idCurso = cursos.id AND materias.id = $idMateria");
    $idCiclo = 0;
    $categoria = 'ESO/BACH';
    if($fila = mysqli_fetch_assoc($result))
    {
        $idCiclo = $fila['id'];
        $categoria = 'FP';
    }
    mysqli_free_result($result);

    // Distinguimos si cargar unos apartados u otros
    $result = mysqli_query($db, "SELECT * FROM apartados_programaciones WHERE categoria='TODOS' OR categoria='$categoria' ORDER BY orden");
    $cont = 0;
    $cont2 = 0;

    while($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $titulo = $fila['titulo'];
        $subapartado = $fila['subapartado'];
        $tipo = $fila['tipo'];
        if (!$subapartado)
        {
            $cont++;
            $cont2 = 0;
            $resultado[] = array('id' => $id, 'tipo' => $tipo, 'nombre' => "$cont. $titulo");
        } else {
            $cont2++;
            $resultado[] = array('id' => $id, 'tipo' => $tipo, 'nombre' => "$cont.$cont2. $titulo");
        }
    }
    mysqli_free_result($result);

    include ('../../includes/database2.php');
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);

?>