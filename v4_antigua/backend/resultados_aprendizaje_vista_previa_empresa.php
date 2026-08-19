<?php
    // Genera un HTML con el resumen de los resultados de aprendizaje por curso y materia
    @session_start();    
    include('includes/database.php');
    include('includes/utilidades.php');
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">                
        <title>Resultados de Aprendizaje</title>
        <link rel="stylesheet" href="../frontend/css/estilos_programaciones.css" />
        <style>
            * {
                text-align: left !important;
            }
        </style>
    </head>
    <body>
                
        <h1>Resultados de Aprendizaje con Formación en Empresa</h1>
        <h2>Listado por ciclo, curso y materia</h2>

        <?php
            // Obtenemos los ciclos ordenados por nombre
            $result = mysqli_query($db, "SELECT * FROM ciclos where familia like '%Informática%' and nivel like '%Ciclo Formativo%' ORDER BY nombre");
            while($fila = mysqli_fetch_assoc($result))
            {
                $totalHoras = 0;
                echo '<h2 style="margin-top:50px; border-bottom:1px solid black; color:green">' . $fila['nombre'] . '</h2>';

                // Obtenemos los cursos de ese ciclo
                $result2 = mysqli_query($db, "SELECT cursos.id, cursos.nombre FROM cursos, cursos_ciclos WHERE cursos.id = cursos_ciclos.idCurso AND cursos_ciclos.idCiclo = " . $fila['id'] . ' ORDER BY cursos_ciclos.orden');
                while($fila2 = mysqli_fetch_assoc($result2))
                {
                    $horasCurso = 0;
                    echo '<h3 style="margin-top:40px; color:darkblue">' . $fila2['nombre'] . '</h3>';
                    // Obtenemos las materias con resultados de aprendizaje de ese curso
                    $result3 = mysqli_query($db, "SELECT DISTINCT materias.id, materias.nombre_oficial, materias.horas_empresa FROM materias WHERE (idDepartamento = 1 or idDepartamento = 2 or idDepartamento = 8) and (materias.idCurso = {$fila2['id']} and materias.horas_empresa > 0)");
                    while($fila3 = mysqli_fetch_assoc($result3))
                    {
                        $horasCurso += $fila3['horas_empresa'];
                        echo '<h4>' . $fila3['nombre_oficial'] . ' (' . $fila3['horas_empresa'] .  ' h. en la empresa)</h4>';
                        echo '<ul>';
                        // Resultados de aprendizaje de cada materia
                        $result4 = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = " . $fila3['id'] . " ORDER BY orden");

                        $modulo = obtenerAcronimo($fila3['nombre_oficial']);

                        while($fila4 = mysqli_fetch_assoc($result4))
                        {
                            if (!empty($fila4['porcentaje_empresa'])) {
                                echo '<li><strong>' . $modulo . ' - RA' . $fila4['orden'] . "</strong>: " . $fila4['texto'] . '</li>';
                            }
                        }
                        mysqli_free_result($result4);
                        echo '</ul>';
                    }
                    mysqli_free_result($result3);

                    echo "<p style='color:darkred'><strong>TOTAL HORAS EMPRESA {$fila2['nombre']}: $horasCurso h.</strong></p>";
                    $totalHoras += $horasCurso;
                }
                mysqli_free_result($result2);
                echo "<p style='color:darkred'><strong>TOTAL HORAS EMPRESA EN EL CICLO: $totalHoras h.</strong></p>";
            }
            mysqli_free_result($result);
        ?>
    </body>
</html>

<?php
    include('includes/database2.php');
?>

