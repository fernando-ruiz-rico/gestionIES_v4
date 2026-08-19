<?php
// Vista principal para la gestión de resultados de aprendizaje
include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Formación en empresa (RA)</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <p><em>Haz clic en el icono del lápiz para editar los datos de cada resultado, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar resultados con el icono de borrar junto a cada uno, y asociarles criterios de evaluación con el botón correspondiente.</em></p>

    <div class="text-center mb-2">
        <a class="btn btn-light" href="resultados_aprendizaje_vista_previa.php" target="_blank"><i class="bi bi-eye"></i> Ver resumen general</a>
        <a class="btn btn-light ms-3" href="resultados_aprendizaje_vista_previa_empresa.php" target="_blank"><i class="bi bi-eye"></i> RAs empresa</a>
        <a class="btn btn-light ms-3" href="criterios_evaluacion_vista_previa_empresa.php" target="_blank"><i class="bi bi-eye"></i> CEs empresa</a>
    </div>

    <select class="form-control" name="seleccionMateria" id="seleccionMateria" onchange="cambiarMateria()">
        <option value="0">--Selecciona una materia--</option>
        <?php
            include('includes/cargar_materias_programaciones.php');
        ?>
    </select> 
    <div id="resultados"></div>

    <?php
    if($permisos)
    {
    ?>
        <div class="text-center mt-2"><button class="btn btn-light" onclick="nuevoResultado()"><i class="bi bi-plus-circle"></i> Nuevo resultado</button></div>
    <?php
    }
    ?>

</div>

<script type="text/javascript">
    var selDepartamento = <?= $_SESSION['departamentoUsuario'] ?>;
</script>

<?php
    // if de comprobación de departamento
    }
    include('modales/resultados_aprendizaje.php');
    include('modales/criterios_evaluacion.php');
?>    

<script src="js/resultados_aprendizaje.js"></script>	

<?php
    include('includes/pie.php');
?>