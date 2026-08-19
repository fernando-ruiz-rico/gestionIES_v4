<?php
    // Vista principal para la gestión de departamentos
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">
    <h1>Departamentos</h1>
    <p><em>Haz clic en el icono del lápiz para editar los datos de cada departamento, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar departamentos con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el departamento si no tiene profesores vinculados al mismo (deberás borrarlos antes).</em></p>
    <!-- En este div se carga por AJAX el listado de departamentos -->
    <div id="listadepartamentos"></div>
    <!-- Botón para abrir el diálogo modal para crear nuevos departamentos -->
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoDepartamento()"><img src="img/add.png" /> Nuevo Departamento</button></div>
</div>

<?php
    // Diálogo modal para crear/editar departamentos
    include('modales/departamentos.php');
?>    
    
<!-- Código JavaScript auxiliar para comunicar con el servidor y pedir datos sobre departamentos -->
<script src="js/departamentos.js"></script>	

<script type="text/javascript">
    // Al finalizar la página cargamos el listado actual de departamentos en el div "listadepartamentos"
    cargarDepartamentos();
</script>

<?php
    include('includes/pie.php');
?>