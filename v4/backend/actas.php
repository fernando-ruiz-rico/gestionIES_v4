<?php
// Página principal de gestión de actas de departamentos
include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Actas de departamentos</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <p><em>Selecciona la fecha del acta que revisar, o bien introduce una nueva con el botón <em>Nueva acta</em>, si eres jefe/a del departamento.</em></p>

    <div class="row">
        <!-- Desplegable para elegir la fecha del acta de reunión. Se rellena desde JavaScript con
             la función cargarActas -->
        <div class="col-sm-8">
            <select class="form-control" name="acta" id="fechasActas" <?=$permisos?'onchange="cambiarActa()"':''?>)>
            </select>
        </div>    
        <div class="col-sm-4" id="generarpdf">
            <!-- Botón para ver el acta en formato PDF -->
            <button class="form-control btn btn-light" onclick="generarPDFActa()"><i class="bi bi-file-earmark-pdf"></i> Generar PDF</button>
        </div>
    </div>
    <br>

    <?php
        if ($permisos)
        {
            // Sólo para quienes tengan permisos de edición
    ?>

    <div class="row" style="text-align:center; margin-bottom: 10px" id="nuevaActa">
        <div class="col">
            <button class="btn btn-light" onclick="nuevaActa()"><i class="bi bi-plus-circle"></i> Nueva Acta</button>
        </div>
    </div>
    <div class="row" id="edicionacta">
        <div class="col">
            <form id="formacta" name="formacta" method="post" enctype="multipart/form-data">
                <!-- En el formulario guardamos el id del departamento, el id del acta,
                    la fecha y el texto de la misma -->
                <input type="hidden" name="idDepartamento" id="idDepartamento" value="<?= $_SESSION['departamentoUsuario'] ?>">
                <input type="hidden" name="idActa" id="idActa" value="">
                <div style="text-align:center; margin-bottom: 10px;">
                    <label for="fecha" class="control-label">Fecha reunión:</label>
                    <input type="text" name="fecha" id="fecha" class="form-control" required>
                </div>
                <textarea name="texto" id="texto" class="textoacta"></textarea>
                <div style="text-align:center"><button class="btn btn-light" type="submit"><i class="bi bi-floppy"></i> Guardar cambios</button></div>
            </form>
        </div>
    </div>

    <?php
    // if de si tiene permisos de edición
    }
    // if de si hay departamento asignado
    }
    ?>

    <!-- Fichero con las funciones para gestión de actas (cargar, insertar, etc) -->
    <script src="js/actas.js?v=1"></script>	

</div>

<?php
include('includes/pie.php');
?>