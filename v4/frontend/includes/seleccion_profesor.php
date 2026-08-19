<label for="seleccionDepartamento" class="control-label mb-2">Elige un profesor:</label>
<select class="form-control mb-2" id="seleccionProfesor" onChange="seleccionarProfesor()">
<option value="0">--Selecciona un profesor--</option>
    
<?php
    $resultados = consultarBaseDeDatos("SELECT * FROM profesores ORDER BY nombre");
    foreach ($resultados as $fila)
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        if(isset($_REQUEST['idProfesor']) && $_REQUEST['idProfesor'] == $id)
        {
            echo '<option value="' . $id . '" selected>' . $nombre . '</option>';
        }
        else
        {
            echo '<option value="' . $id . '">' . $nombre . '</option>';
        }
    }
?>    

</select>