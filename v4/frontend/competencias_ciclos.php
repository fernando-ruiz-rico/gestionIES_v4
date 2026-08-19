<?php
    // Vista principal para la gestión de competencias por ciclo
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Competencias</h1>

    <select class="form-control" id="ciclos" onChange="seleccionarCiclo()">
        <option value="0">--Selecciona un ciclo--</option>
        
        <?php
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM ciclos ORDER BY nombre");
            while($fila = mysqli_fetch_assoc($result))
            {
                $id = $fila['id'];
                $nombre = $fila['nombre'];
                echo '<option value="' . $id . '">' . $nombre . '</option>';
            }
            mysqli_free_result($result);
            include('includes/database2.php');
        ?>
        
    </select>

    <br>
    <p><em>Arrastra las competencias para ordenarlas entre sí. Haz clic en el icono del lápiz para editar los datos de cada competencia, y en el icono de Nueva al final para añadir nuevas. Puedes eliminar competencias con el icono de borrar junto a cada apartado.</em></p>
    <div id="listacompetencias"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevaCompetencia()"><i class="bi bi-plus"></i> Nueva Competencia</button></div>

</div>

<?php
    include('modales/competencias_ciclos.php');
?>    
    
<script src="js/competencias_ciclos.js"></script>	

<?php
    include('includes/pie.php');
?>
