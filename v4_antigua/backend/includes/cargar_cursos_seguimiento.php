<?php
@session_start();

if (isset($_SESSION['idUsuario'])) 
{
    $cursoActual = cursoActual();
    $cursos = consultarBaseDeDatos("SELECT DISTINCT curso FROM seguimiento_programaciones ORDER BY curso");
    $existeCursoActual = false;
    
    foreach ($cursos as $fila) {
        $c = $fila['curso'];
        echo '<option value="' . $c . '">' . $c . '</option>';
        if ($c == $cursoActual) $existeCursoActual = true;
    }
    if (!$existeCursoActual) echo '<option value="' . $cursoActual . '">' . $cursoActual . '</option>';
}
?>