<?php
    // Vista principal para la gestión de cualificaciones profesionales y
    // unidades de competencia asociadas
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <div class="row">
        <div class="col-md-6">
            <button class="btn btn-light form-control" onclick="cargarCualificaciones()">Cualificaciones</button>
        </div>
        <div class="col-md-6">
            <button class="btn btn-light form-control" onclick="cargarUnidades()">Unidades de Competencia</button>
        </div>
    </div>

    <div id="listaprincipal"></div>
    <!--<div style="text-align: center"><button class="btn btn-light" onclick="nuevoCiclo()"><i class="bi bi-plus"></i> Nuevo Ciclo</button></div>-->
</div>

<?php
    include('modales/cualificaciones.php');
    include('modales/unidades_competencia.php');
    include('modales/cualificaciones_unidades.php');
?>    
    
<script src="js/cualificaciones_uc.js"></script>	

<?php
    include('includes/pie.php');
?>
