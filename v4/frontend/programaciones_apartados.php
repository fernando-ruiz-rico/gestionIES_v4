
<?php
    // Vista principal para la gestión de apartados de programaciones
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Apartados de las programaciones</h1>

    <p><em>Arrastra cada apartado para ordenarlo respecto al resto. Haz clic en el icono del lápiz para editar los datos de cada apartado, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar apartados con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el apartado, sin borrar los subapartados que pueda contener (deberán borrarse manualmente después, si se desea).</em></p>
    <div id="apartadosprog"></div>
    <div style="text-align: center"><button class="btn btn-default" onclick="nuevoApartado()"><img src="img/add.png" /> Nuevo apartado</button></div>

</div>

<?php
    include('modales/programaciones_apartados.php');
?>    
    
<script src="js/programaciones_apartados.js"></script>	

<?php
    include('includes/pie.php');
?>
