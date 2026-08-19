<?php
// Página principal de gestión de contenidos de las programaciones

include('includes/cabecera.php');
include('includes/utilidades.php');
  
$cursoActual = cursoActual();
echo '<script language="javascript" type="text/javascript">var cursoActual = "' . $cursoActual . '";</script>';
$existeCursoActual = false;
?>

<div class="panelcentral">

    <h1>Seguimiento de programaciones</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <?php
        if ($permisos) 
        {
    ?> 

    <select class="form-control" name="cursoSeguimiento" id="cursoSeguimiento" onchange="ejecutarOperacionSeleccionada('cargarDatos')">
        <option value="0">--Selecciona una curso académico--</option>
        <?php
            include('includes/database.php');

            $result = mysqli_query($db, "SELECT DISTINCT curso FROM seguimiento_programaciones ORDER BY curso");
            while($fila = mysqli_fetch_assoc($result))
            {
                $curso = $fila['curso'];
                echo '<option value="' . $curso . '">' . $curso . '</option>';
                if ($curso == $cursoActual)
                    $existeCursoActual = true;
            }

            if (!$existeCursoActual)
                echo '<option value="' . $cursoActual . '">' . $cursoActual . '</option>';
        ?>
    </select>

    <?php
        } else {
    ?>
        <p><strong>Curso actual</strong>: <?=$cursoActual?></p>        
    <?php
        }
    ?>
                       
    <select class="form-control mb-2" name="evaluacionSeguimiento" id="evaluacionSeguimiento" onchange="ejecutarOperacionSeleccionada('cargarDatos')">
        <option value="0">--Selecciona una evaluación--</option>
        <?php
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM evaluaciones");
            while ($fila = mysqli_fetch_assoc($result))
            {
                echo '<option value="' . $fila['id'] . '">' . $fila['nombre'] . '</option>';
            }
            include('includes/database2.php');
        ?>
    </select>

    <?php
        if ($permisos) 
        {
    ?>

    <h2>Edición de seguimiento de elementos comunes</h2>

    <div class="row">
        <div class="col-md-3">
            <button class="btn btn-light form-control" onclick="ejecutarOperacionSeleccionada('importarEvaluacionComun')"><img src="img/import.png"/> Importar anterior evaluación</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-light form-control" onclick="ejecutarOperacionSeleccionada('importarCursoAnteriorComun')"><img src="img/import.png"/> Importar anterior curso</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-light form-control" onclick="ejecutarOperacionSeleccionada('vistaPreviaComun')"><img src="img/preview.png"/> Vista previa</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-light form-control" onclick="generarPDFSeguimiento()"><i class="bi bi-file-earmark-pdf"></i> Generar PDF</button>
        </div>
    </div>

    <div id="edicionseguimientocomun">
        <form id="formseguimientocomun" name="formseguimientocomun" method="post" enctype="multipart/form-data">
            <input type="hidden" name="curso" id="cursoComun" value="" />
            <input type="hidden" name="evaluacion" id="evaluacionComun" value="" />
            <label class="control-label" for="funcionamiento_departamento">Funcionamiento del departamento y propuestas de mejora.</label>
            <textarea name="funcionamiento_departamento" class="form-control seguimiento" rows="5" id="funcionamiento_departamento"></textarea>
            <label class="control-label" for="actividades">Actividades extraescolares</label>
            <textarea name="actividades" class="form-control seguimiento" rows="5" id="actividades"></textarea>
            <label class="control-label" for="temporalizacion_defecto">Temporalización (contenido por defecto)</label>
            <textarea name="temporalizacion_defecto" class="form-control seguimiento" rows="5" id="temporalizacion_defecto"></textarea>
            <div style="text-align:center"><button class="btn btn-light" type="submit"><i class="bi bi-floppy"></i> Guardar cambios</button></div>
        </form>
    </div> 

<?php
    // if de comprobación de permisos adicionales
    }
    // if de comprobación de departamento
    }
?>

</div>

<script src="js/programaciones_seguimiento.js"></script>	

<?php
include('includes/pie.php');
?>