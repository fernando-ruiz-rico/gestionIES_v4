<?php
    // Vista principal para la gestión de departamentos
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Especialidades por departamento</h1>

    <?php
        // Desplegable para elegir el departamento sobre el que trabajar
        include('includes/seleccion_departamento.php');
    ?>

    <br>
    <p><em>Haz clic en el icono del lápiz para editar los datos de cada especialidad, y en el icono de Nueva al final para añadir nuevas. Puedes eliminar especialidades con el icono de borrar junto a cada apartado.</em></p>
    <!-- En este div se carga por AJAX el listado de especialidades -->
    <div id="listaespecialidades"></div>
    <!-- Botón para abrir el diálogo modal para crear nuevas especialidades -->
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevaEspecialidad()"><i class="bi bi-plus-circle"></i> Nueva Especialidad</button></div>
</div>
  
<?php
    // Diálogo modal para crear/editar especialidades
    include('modales/especialidades.php');
?>    

<!-- Código JavaScript auxiliar para comunicar con el servidor y pedir datos sobre especialidades -->
<script src="js/especialidades.js"></script>	

<?php
    include('includes/pie.php');
?>