<?php
    // Vista principal para la gestión de grupos por curso
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Grupos por curso</h1>

    <select class="form-control" id="cursosgrupos" onChange="seleccionarCursoGrupo()">
        <option value="0">--Selecciona un curso--</option>
        
        <?php
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM cursos ORDER BY orden");
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
    <p><em>Arrastra los grupos para ordenarlos entre sí. Haz clic en el icono del lápiz para editar los datos de cada grupo, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar grupos con el icono de borrar junto a cada apartado.</em></p>
    <div id="listagrupos"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoGrupo()"><i class="bi bi-plus"></i> Nuevo Grupo</button></div>

</div>

<?php
    include('modales/grupos.php');
?>    
    
<script src="js/grupos.js"></script>	

<?php
    include('includes/pie.php');
?>
