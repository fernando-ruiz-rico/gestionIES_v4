<?php
    // Vista principal para la gestión de cursos
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Materias por curso</h1>

    <select class="form-control" id="cursosmaterias" onChange="seleccionarCursoMateria()">
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
    <p><em>Haz clic en el icono del lápiz para editar los datos de cada materia, y en el icono de Nueva al final para añadir nuevas. Puedes eliminar materias con el icono de borrar junto a cada apartado.</em></p>
    <div id="listamaterias"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevaMateria()"><i class="bi bi-plus-circle"></i> Nueva Materia</button></div>
</div>

<?php
    include('modales/materias.php');
    include('modales/materias_grupos.php');
    include('modales/competencias_materia.php');
?>    
    
<script src="js/materias.js"></script>	

<?php
    include('includes/pie.php');
?>
