<?php

// Muestra un listado de "checkboxes" para elegir los departamentos asignados
// a un escenario de desideratas, de modo que los profesores de esos departamentos
// podrán elegir sus materias desde dicho escenario

@session_start();

if(isset($_SESSION['departamentoUsuario']))
{
    include ('../../includes/database.php');

    $result = mysqli_query($db, "SELECT * FROM departamentos ORDER BY nombre");

    while ($fila = mysqli_fetch_assoc($result))
    {
        // Recorremos cada departamento
        $existe = false;
        if (!empty($_REQUEST['idEscenario']))
        {
            // Buscamos si ya está asignado al escenario actual
            $result2 = mysqli_query($db, "SELECT * FROM departamentos_escenarios WHERE idEscenario = " . $_REQUEST['idEscenario'] . " AND idDepartamento = " . $fila['id']);
            if (mysqli_num_rows($result2) > 0)
                $existe = true;
            mysqli_free_result($result2);
        }
        
        // Marcamos la casilla de checkbox si ya está asignado, o si el departamento es el del usuario que crea el escenario
        if ($fila['id'] == $_SESSION['departamentoUsuario'] || $existe)
            echo '<input type="checkbox" name="departamentoEscenario[]" id="dep' . $fila['id'] . '" value="' . $fila['id'] . '" checked />&nbsp;' . $fila['nombre'] . '<br />';
        else
            echo '<input type="checkbox" name="departamentoEscenario[]" id="dep' . $fila['id'] . '" value="' . $fila['id'] . '" />&nbsp;' . $fila['nombre'] . '<br />';
    }

    mysqli_free_result($result);    

    include ('../../includes/database2.php');
}

?>