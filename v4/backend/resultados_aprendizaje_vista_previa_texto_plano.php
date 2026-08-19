<?php
    // Genera un HTML con el resumen de los resultados de aprendizaje por curso y materia
    @session_start();    
    include('includes/database.php');
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">                
        <title>Resultados de Aprendizaje</title>
        <link rel="stylesheet" href="../frontend/css/estilos_programaciones.css" />
    </head>
    <body>
                
        <h1>Resultados de aprendizaje</h1>
        <h2>Listado por ciclo, curso y materia</h2>

        <?php
            // Obtenemos los ciclos ordenados por nombre
            $result = mysqli_query($db, "SELECT * FROM ciclos ORDER BY nombre");
            while($fila = mysqli_fetch_assoc($result))
            {
                $totalHoras = 0;
                echo '<h3 style="border-bottom:1px solid black">' . $fila['nombre'] . '</h3>';

                // Obtenemos los cursos de ese ciclo
                $result2 = mysqli_query($db, "SELECT cursos.id, cursos.nombre FROM cursos, cursos_ciclos WHERE cursos.id = cursos_ciclos.idCurso AND cursos_ciclos.idCiclo = " . $fila['id'] . ' ORDER BY cursos_ciclos.orden');
                while($fila2 = mysqli_fetch_assoc($result2))
                {
                    $horasCurso = 0;
                    echo '<h4>' . $fila2['nombre'] . '</h4>';
                    // Obtenemos las materias con resultados de aprendizaje de ese curso
                    $result3 = mysqli_query($db, "SELECT DISTINCT materias.id, materias.nombre, materias.nombre_oficial, materias.horas_empresa FROM materias WHERE materias.idCurso = " . $fila2['id']);
                    while($fila3 = mysqli_fetch_assoc($result3))
                    {
                        $horasCurso += $fila3['horas_empresa'];
                        echo '<h5>' . $fila3['nombre_oficial'] . '</h5>';
                        echo '<blockquote>';
                        // Resultados de aprendizaje de cada materia
                        $result4 = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = " . $fila3['id'] . " ORDER BY orden");
                        $totalPorcentaje = 0;
                        $totalResultados = 0;
                        while($fila4 = mysqli_fetch_assoc($result4))
                        {
                            $totalResultados++;
                            $totalPorcentaje += $fila4['porcentaje_empresa'];
                            echo '<p>RA' . $fila4['orden'] . ". " . $fila4['texto'] . '</p>';
                        }
                        mysqli_free_result($result4);
                        $media = $totalResultados == 0?0:$totalPorcentaje / $totalResultados;
                        // Mostramos en rojo porcentajes de R.A. fuera de límites recomendables
                        echo '</blockquote>';
                    }
                    mysqli_free_result($result3);

                    $totalHoras += $horasCurso;
                }
                mysqli_free_result($result2);
            }
            mysqli_free_result($result);
        ?>
    </body>
</html>

<?php
    include('includes/database2.php');
?>

