<?php

// Vista principal para la gestión de selección de materias en las desideratas

// Carga de la cabecera común a todas las vistas principales
include('includes/cabecera.php');

// Mensaje de ayuda contextual que se abre en el modal con el botón de "Ayuda"
$mensajeAyuda = 
    "<h2>Ayuda rápida</h2>" .
    "<p>Selecciona las opciones que desees del panel de <em>Cursos</em>" .  
    " haciendo clic en el botón + de cada opción para añadirlo a tu selección" . 
    " (panel <em>Selección</em>). Automáticamente se calcularán las horas totales" . 
    " acumuladas. También puedes quitar un elemento de tu selección eligiéndolo en " . 
    " en el panel <em>Selección</em> y pulsando el icono de la papelera bajo el listado." . 
    " Finalmente, puedes vaciar toda tu selección pulsando el icono correspondiente," . 
    " junto a la papelera.</p>" . 
    "<p><strong>IMPORTANTE:</strong> la selección hecha por cada profesor en esta" . 
    " aplicación es meramente orientativa, sujeta a aprobación final por parte del" . 
    " departamento tras el claustro de desideratas. El hecho de elegir un módulo antes" . 
    " que otro compañero/a no da ninguna preferencia sobre él. A la hora de resolver" . 
    " posibles conflictos se tendrán en cuenta, por un lado, el orden de elección de" . 
    " cada profesor/a en función a su antigüedad en el cuerpo, y por otro, el orden" . 
    " de preferencia del módulo para cada profesor.</p>" . 
    "<p><em>EJEMPLO</em>: si primer profesor en elegir selecciona el módulo de <em>" . 
    " Despliegue de Aplicaciones Web</em> como segunda opción, y el octavo profesor" . 
    " lo elige como primera opción, tendría preferencia este octavo profesor, al ser" . 
    " su primera elección. En cambio. si el primer profesor lo marca también como" . 
    " primera elección, tendría preferencia este profesor, al estar por delante en el" . 
    " turno de elecciones</p>";
?>

<div class="panelcentral">

    <h1>
        Selección de materias 
        <button class="btn btn-primary" onclick="mostrarMensaje('<?=$mensajeAyuda?>')">Ayuda</button>
        <?php
        if (isset($_SESSION['departamentoUsuario']) && $_SESSION['departamentoUsuario'] == 1)
        {
        ?>
            <button class="btn btn-warning" onclick="window.open('https://drive.google.com/drive/folders/1iYJsbhwJztutv5540yFHA_uYaCSrtX1i?usp=sharing', '_blank')">Documentación</button>
        <?php
        }
        ?>
    </h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <div>
        <strong>Escenario:</strong>
        <select name="escenario" class="form-control" id="escenario" onChange="seleccionarEscenario()">
            <option value="-1" selected>--Selecciona un escenario--</option>
        <?php
            include('includes/database.php');
            // Si tiene permisos de edición podrá ver todos los escenarios del departamento actual
            if ($permisos)
                $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE id IN (SELECT idEscenario FROM departamentos_escenarios WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . ")");
            // En caso contrario sólo podrá ver los escenarios que estén activos para elegir materias
            else
                $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE activo_desideratas = 1 AND id IN (SELECT idEscenario FROM departamentos_escenarios WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . ")");
            
            while ($fila = mysqli_fetch_assoc($result))
            {
                echo '<option value="' . $fila['id'] . '">' . $fila['nombre'] . '</option>';
            }
            mysqli_free_result($result);
            include('includes/database2.php');
        ?>
        </select>
    </div>

    <?php
    if ($permisos)
    {
        // Los admins y jefes de departamento verán 3 paneles: profesores, materias y selección del profesor elegido
        // Los profesores verán sólo 2: materias y selección del profesor actual
    ?>        

    <!-- Panel de profesores (sólo para administradores y jefes de departamento) -->        

    <div class="izquierda panelseleccion" id="profesoresDesideratas">
        <h3>Profesores</h3>
        <p style="text-align:center">
            <?php
                include('includes/database.php');
                $result = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . " ORDER BY id");
                while ($fila = mysqli_fetch_assoc($result))
                    echo "<input type=\"radio\" name=\"especialidad\" onclick=\"cambiarEspecialidad('" . $fila['id'] . "')\"/> " . $fila['id'] . "&nbsp;&nbsp;";
                echo "<input type=\"radio\" name=\"especialidad\" onclick=\"cambiarEspecialidad('Todos')\" checked /> Todos";
                mysqli_free_result($result);
                include('includes/database2.php');
            ?>
        </p>
        <p style="text-align:center">
            <i class="bi bi-printer-fill"></i>&nbsp;&nbsp;&nbsp;&nbsp;
            <i class="bi bi-calendar-week"></i>&nbsp;&nbsp;&nbsp;&nbsp;
            <i class="bi bi-arrow-counterclockwise"></i>&nbsp;&nbsp;&nbsp;&nbsp;
        </p>
        <div id="listaprof">
        </div>
    </div>

    <!-- Panel de cursos para admins y jefes de departamento (menos anchura en CSS porque hay 3 columnas) -->
    <div class="izquierda panelseleccion" id="cursosDesideratas">

    <?php
    } else {
    ?>
    <!-- Panel de cursos para profesores (más anchura en CSS al haber sólo 2 columnas) -->
    <div class="izquierda panelseleccion" id="cursosProfesorDesideratas">
    <?php
    }
    ?>    

    <h3>Cursos</h3>
    <p>Haz clic en cada curso para ver sus asignaturas. Haz clic en el botón '+' de una asignatura para añadirla a la lista del profesor seleccionado.</p>
    <div id="listacur">                
    </div>
    </div>

    <!-- Seleccion de materias -->

    <div class="derecha panelseleccion" id="seleccionDesideratas">
        <h3>Selección</h3>
        <p>Puedes reordenar tus prioridades arrastrando las asignaturas entre ellas
        <p id="profsel"></p>
        <div id="listasel">
        </div>
        <div id="totalsel">
        </div>
        <p style="text-align:center" id="botonsel">
        </p>
    </div>

    <?php    
    include('modales/selecciones_materia.php');
    include('modales/horas_seleccion.php');
    // if de selección de departamento
    }
    ?>

    <script src="js/seleccion.js"></script>	
    <script type="text/javascript">
        selProf = <?= $_SESSION['idUsuario'] != 'admin'?$_SESSION['idUsuario']:-1?>;
        selEspecialidadProf = '<?= empty ($_SESSION['especialidadUsuario'])?"":$_SESSION['especialidadUsuario'] ?>';
    </script>

<?php
    include('includes/pie.php');
?>
