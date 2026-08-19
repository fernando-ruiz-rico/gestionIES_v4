<?php
    // Vista principal para la gestión de cursos
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Cursos</h1>

    <p><em>Arrastra los cursos para ordenarlos entre sí. Haz clic en el icono del lápiz para editar los datos de cada curso, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar cursos con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el curso si no tiene grupos ni materias asociadas (deberás borrarlas antes).</em></p>
    <div id="listacursos"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoCurso()"><i class="bi bi-plus-circle"></i> Nuevo Curso</button></div>
</div>

<?php
    include('modales/cursos.php');
?>    
    
<script src="js/cursos.js"></script>	

<script type="text/javascript">
    cargarCursos();
</script>

<?php
    include('includes/pie.php');
?>
