<?php
    // Vista principal para la gestión de cursos
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Ciclos formativos</h1>

    <div id="listaciclos"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoCiclo()"><i class="bi bi-plus"></i> Nuevo Ciclo</button></div>
</div>

<?php
    include('modales/ciclos.php');
    include('modales/unidades_ciclos.php');
    include('modales/cursos_ciclos.php');
?>    
    
<script src="js/ciclos.js"></script>	

<script type="text/javascript">
    cargarCiclos();
</script>

<?php
    include('includes/pie.php');
?>
