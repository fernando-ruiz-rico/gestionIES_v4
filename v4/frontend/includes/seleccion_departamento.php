<!-- Include para mostrar un desplegable con los departamentos en algunas vistas que lo comparten -->
<label for="seleccionDepartamento" class="control-label mb-2">Elige el departamento:</label>
<select class="form-control mb-2" id="seleccionDepartamento" onChange="seleccionarDepartamento()">
    <option value="0">--Selecciona un departamento--</option>
    
    <?php
        include('includes/database.php');
        $result = mysqli_query($db, "SELECT * FROM departamentos ORDER BY nombre");
        while($fila = mysqli_fetch_assoc($result))
        {
            $id = $fila['id'];
            $nombre = $fila['nombre'];
            if(isset($_SESSION['departamentoUsuario']) && $_SESSION['departamentoUsuario'] == $id)
            {
                echo '<option value="' . $id . '" selected>' . $nombre . '</option>';
            }
            else
            {
                echo '<option value="' . $id . '">' . $nombre . '</option>';
            }
        }
        mysqli_free_result($result);
        include('includes/database2.php');
    ?>
    
</select>
