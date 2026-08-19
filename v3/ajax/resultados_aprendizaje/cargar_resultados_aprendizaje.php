<?php

// Devuelve un listado HTML de los resultados de aprendizaje asociados a una materia determinada

@session_start();

if(!empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');

    // Guardamos también si el usuario actual tiene permisos de edición generales (admins o jefes de departamento)
    $permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

    // Obtenemos también cuántas horas se impartirán en la empresa para la materia elegida
    $result = mysqli_query($db, "SELECT horas_empresa FROM materias WHERE id = " . $_REQUEST['idMateria']);
    $fila = mysqli_fetch_assoc($result);
    $horas = $fila['horas_empresa'];
    mysqli_free_result($result);

    // Pequeño formulario al inicio para indicar las horas de docencia que se asignan a la empresa
    echo '<div class="text-center my-2">';
    echo '<label for="horasEmpresa" class="me-2">Horas a impartir en empresa:</label>';
    $disabled = $permisos ? '' : 'disabled';
    echo '<input type="number" class="form-control" style="display:inline;width:100px" name="horasEmpresa" id="horasEmpresa" value="' . $horas . '" ' . $disabled . '>';
    echo '<button class="btn btn-light ms-2" type="button" onclick="actualizarHorasEmpresa(' . $_REQUEST['idMateria'] . ')">Actualizar</button>';
    echo '</div>';

    $result = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = " . $_REQUEST['idMateria'] . " ORDER BY orden");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $orden = $fila['orden'];
        $texto = $fila['texto'];
        $porcentaje = $fila['porcentaje_empresa'];
        echo '<div class="listado claro izquierda">';
        echo '<div class="izquierda resultado">'. $orden . '. ' . $texto . ' <em>(' . $porcentaje . '% empresa)</em></div>';
        echo '<div class="derecha">';
        if($permisos)
        {
            // Botón para editar criterios de evaluación
            echo '<button title="Asociar criterios de evaluación" class="btn btn-light" onclick="asociarCriterios(' . $id . ')"><img src="img/fill.png"></button>';
            // Botón para borrar
            echo '<button class="btn btn-light" onclick="borrarResultado(' . $id . ",'" . $texto . "'" . ')"><img src="img/delete.png"></button>';
        }
        // Botón de editar
        echo '<button class="btn btn-light" onclick="cargarResultadoModal(' . $id . ')"><img src="img/edit.png"></button>';
        echo '</div>';
        echo '</div>';
    }
    
    mysqli_free_result($result);
    
    include ('../../includes/database2.php');    
}

?>
