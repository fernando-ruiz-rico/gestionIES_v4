<?php
    // Genera un HTML con el contenido de la programación para la materia indicada
    // Se escriben en rojo los contenidos obligatorios no rellenos, y en azul los no obligatorios
    // que no se han rellenado

    if (empty($_REQUEST['idMateria'])) die("Materia no seleccionada.");
    
    include('includes/database.php');
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">                
        <title>Programación didáctica</title>
        <link rel="stylesheet" href="../frontend/css/estilos_programaciones.css" />
    </head>
    <body>
        
        <p style="color: gray">
            <span style="background-color: red">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp; Apartados vacíos que es obligatorio rellenar
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span style="background-color: blue">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp; Apartados vacíos que no es obligatorio rellenar
        </p>
                  
        
        <!-- TITULO -->
        
        <?php
            $result = mysqli_query($db, "SELECT materias.nombre AS nommat, materias.idDepartamento, cursos.nombre AS nomcur, cursos.categoria AS categoria FROM materias, cursos WHERE materias.idCurso = cursos.id AND materias.id = " . $_REQUEST['idMateria']);
            $fila = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $idDepartamento = $fila['idDepartamento'];
            $categoria = $fila['categoria'];
            if(empty($fila['categoria']))
                $categoria = '';
        ?>
        <div class="titulo"><?=$fila['nommat']?></div>
        <div class="titulo"><?=$fila['nomcur']?></div>
        <div class="subtitulo">Programación didáctica</div>

        <!-- INDICE (vuelta 1) y CONTENIDO (vuelta 2) -->
        
        <?php
            for ($i = 0; $i < 2; $i++)
            {
                $result = mysqli_query($db, "SELECT * FROM apartados_programaciones WHERE categoria IS NULL OR categoria = 'TODOS' OR categoria = '$categoria' ORDER BY orden");
                $cont = 0;
                $cont2 = 0;
                while($fila = mysqli_fetch_assoc($result))
                {
                    $id = $fila['id'];
                    $requerido = $fila['requerido'];

                    $result2 = mysqli_query($db, "SELECT texto FROM contenidos_programaciones WHERE idApartado = $id AND idMateria = " . $_REQUEST['idMateria']);
                    $existeContenido = FALSE;
                    $texto = "";
                    if (mysqli_num_rows($result2) > 0)
                    {
                        $fila2 = mysqli_fetch_assoc($result2);
                        if (strlen(trim($fila2['texto'])) > 0)
                        {
                            $texto = $fila2['texto'];
                            $existeContenido = TRUE;
                        }
                    }
                    mysqli_free_result($result2);
                    
                    if (!$existeContenido)
                    {
                        $result2 = mysqli_query($db, "SELECT texto FROM contenidos_defecto_programaciones WHERE idDepartamento = $idDepartamento AND idApartado = $id");
                        if (mysqli_num_rows($result2) > 0)
                        {
                            $fila2 = mysqli_fetch_assoc($result2);
                            if (strlen(trim($fila2['texto']))> 0)
                            {
                                $texto = $fila2['texto'];
                                $existeContenido = true;
                            }
                        }
                        mysqli_free_result($result2);
                    }

                    $clase="";
                    if (!$existeContenido && $requerido)
                        $clase = ' errorprog';
                    else if (!$existeContenido && !$requerido)
                        $clase = ' warningprog';

                    if (!$fila['subapartado'])
                    {
                        $cont2 = 0;
                        $cont++;
                        if ($i == 0)
                            echo '<p class="indice' . $clase . '">' . $cont . '.' . '<a href="#c' . $id . '">' . $fila['titulo'] . '</a></p>';
                        else
                        {
                            echo '<h1' . $clase . '>' . $cont . '.' . '<a name="c' . $id . '">' . $fila['titulo'] . '</a></h1>';
                            if (isset($texto)) echo $texto;
                        }
                    } else {
                        $cont2++;
                        if ($i == 0)
                            echo '<blockquote class="indice' . $clase . '">' . $cont . '.' . $cont2 . '.' . '<a href="#c' . $id . '">' . $fila['titulo'] . '</a></blockquote>';
                        else
                        {
                            echo '<h2' . $clase . '>' . $cont . '.' . $cont2 . '.' . '<a name="c' . $id . '">' . $fila['titulo'] . '</a></h2>';
                            if (isset($texto)) echo $texto;
                        }
                    }
                }
                mysqli_free_result($result);
            }
        ?>
                
    </body>
</html>

<?php
    include('includes/database2.php');
?>

