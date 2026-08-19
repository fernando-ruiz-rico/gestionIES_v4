<?php    
    // Muestra una vista previa del seguimiento de programaciones, para un curso, evaluación y materia
    // En caso de no recibir un id de materia, se muestran los apartados por defecto del seguimiento
    @session_start();
    include('includes/database.php');
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">                
        <title>Seguimiento programación didáctica</title>
        <link rel="stylesheet" href="../frontend/css/estilos_programaciones.css" />
    </head>
    <body>
        
        <!-- TITULO -->
        
        <?php
            if (!empty($_REQUEST['idMateria']))
                $result = mysqli_query($db, "SELECT materias.nombre AS nommat, seguimiento_programaciones.temporalizacion, seguimiento_programaciones.resultados, seguimiento_programaciones.resultados_porcentaje FROM materias, seguimiento_programaciones WHERE materias.id = seguimiento_programaciones.idMateria AND materias.id = " . $_REQUEST['idMateria'] . " AND curso = '" . $_REQUEST['curso'] . "' AND evaluacion = " . $_REQUEST['evaluacion']);
            else
                $result = mysqli_query($db, "SELECT seguimiento_programaciones_departamento.funcionamiento_departamento, seguimiento_programaciones_departamento.actividades_extraescolares, seguimiento_programaciones_departamento.temporalizacion_defecto FROM seguimiento_programaciones_departamento WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND curso = '" . $_REQUEST['curso'] . "' AND evaluacion = " . $_REQUEST['evaluacion']);
            $fila = mysqli_fetch_assoc($result);
        ?>
        <div class="titulo">Seguimiento de programaciones</div>
        <div class="titulo">Curso <?=$_REQUEST['curso'];?>, <?=$_REQUEST['evaluacion'];?>ª evaluación</div>
        <?php
            if (!empty($fila['nommat']))
            {
                echo '<div class="subtitulo">' . $fila['nommat'] . '</div>';
                echo '<h1>Temporalización</h1>';
                echo '<div>' . $fila['temporalizacion'] . '</div>';
                echo '<h1>Resultados</h1>';
                echo '<div>' . $fila['resultados'] . '</div>';
                echo '<p><strong>Porcentaje de aprobados: ' . $fila['resultados_porcentaje'] . ' %</strong</p>';
            } else {
                echo '<h1>Actividades extraescolares programadas</h1>';
                echo '<div>' . $fila['actividades_extraescolares'] . '</div>';                
                echo '<h1>Temporalización (contenido por defecto)</h1>';
                echo '<div>' . $fila['temporalizacion_defecto'] . '</div>';                
                echo '<h1>Funcionamiento del departamento y propuestas de mejora</h1>';
                echo '<div>' . $fila['funcionamiento_departamento'] . '</div>';
            }
        ?>
                
    </body>
</html>

<?php
    include('includes/database2.php');
?>

