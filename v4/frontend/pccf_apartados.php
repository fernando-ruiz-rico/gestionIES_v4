
<?php
    // Vista principal para la gestión de apartados de PCCF
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Apartados de los PCCF</h1>

    <p><em>Arrastra cada apartado para ordenarlo respecto al resto. Haz clic en el icono del lápiz para editar los datos de cada apartado, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar apartados con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el apartado, sin borrar los subapartados que pueda contener (deberán borrarse manualmente después, si se desea).</em></p>
    <div id="apartadospccf"></div>
    <div style="text-align: center"><button class="btn btn-default" onclick="nuevoApartado()"><i class="bi bi-plus"></i> Nuevo apartado</button></div>

</div>

<?php
    include('modales/pccf_apartados.php');
?>    
    
<script src="js/pccf_apartados.js"></script>	

<?php
    include('includes/pie.php');
?>
