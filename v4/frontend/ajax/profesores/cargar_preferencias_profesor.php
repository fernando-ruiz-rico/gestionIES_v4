<?php

// Carga las preferencias horarias del profesor que se recibe

include('../../includes/database.php');

// Preparamos los días de la semana y las horas de mañana y de tarde
$dias = array('L', 'M', 'X', 'J', 'V');
$horasM = mysqli_query($db, "SELECT * FROM horas WHERE turno='M' ORDER BY hora");
$horasT = mysqli_query($db, "SELECT * FROM horas WHERE turno='T' ORDER BY hora");
// Inicializamos a vacías las preferencias rojas y amarillas del profesor
$prefRojas = "";
$prefAmarillas = "";
    
// Variable para la restricción de límite del número máximo de casillas rojas
$contRojas = 0;

if (!empty($_REQUEST['idProfesor']))
{
    // Buscamos las preferencias del profesor
    $resultHoras = mysqli_query($db, "SELECT * FROM preferencias_horario WHERE idProfesor = " . $_REQUEST['idProfesor']);
    // Guardamos preferencias en dos cadenas (rojas y amarillas)
    while ($fila = mysqli_fetch_assoc($resultHoras))
    {
        $idPref = $fila['dia'] . str_replace(':', '_', $fila['hora']);
        if ($fila['preferencia'] == 'R')
        {
            $prefRojas .= $idPref;
            $contRojas++;
        }
        else
            $prefAmarillas .= $idPref;
    }  
    mysqli_free_result($resultHoras);
}
    
// Generamos la tabla que se carga en el perfil del profesor

echo '<table class="preferencias">';
        
?>

<script type="text/javascript">
    // Guardamos en los campos "hidden" las preferencias actuales del profesor
    $('#prefRojas').val('<?=$prefRojas?>');
    $('#prefAmarillas').val('<?=$prefAmarillas?>');
    
    // Restricción con el máximo número de casillas rojas permitidas
    // Cambiar valor de variable "maxRojas" para permitir otro máximo
    var maxRojas = 3;
    var contRojas = <?php echo $contRojas; ?>;
</script>

<?php
        
    // Cabecero de días
    
    echo '<tr><th></th>';
    foreach ($dias as $dia)
    {
        echo "<th>$dia</th>";
    }
    echo '</tr>';
    
    // Filas de horas (mañana)
    
    while ($fila = mysqli_fetch_assoc($horasM))
    {
        echo '<tr>';
        echo '<th>' . $fila['hora'] . '</th>';
        foreach ($dias as $dia)
        {
            $idCelda = $dia . str_replace(':', '_', $fila['hora']);
            $clase = "pref";
            if (strpos($prefRojas, $idCelda) !== FALSE)
                $clase .= " rojo";
            else if (strpos($prefAmarillas, $idCelda) !== FALSE)
                $clase .= " amarillo";
            echo '<td class="' . $clase . '" id="' . $idCelda . '"></td>';
        }
        echo '</tr>';
    }
    
    // Separación
    echo '<tr><th class="muyoscuro">&nbsp;</th>';
    foreach ($dias as $dia)
    {
        echo '<td class="muyoscuro"></td>';
    }
    echo '</tr>';

    // Filas de horas (tarde)
    
    while ($fila = mysqli_fetch_assoc($horasT))
    {
        echo '<tr>';
        echo '<td><strong>' . $fila['hora'] . '</strong></td>';
        foreach ($dias as $dia)
        {
            $idCelda = $dia . str_replace(':', '_', $fila['hora']);
            $clase = "pref";
            if (strpos($prefRojas, $idCelda) !== FALSE)
                $clase .= " rojo";
            else if (strpos($prefAmarillas, $idCelda) !== FALSE)
                $clase .= " amarillo";
            echo '<td class="' . $clase . '" id="' . $idCelda . '"></td>';
        }
        echo '</tr>';
    }
    
    echo '</table><br/><br/>';

    mysqli_free_result($horasM);
    mysqli_free_result($horasT);

include ('../../includes/database2.php');

?>

<script type="text/javascript">

    // Evento de clic sobre cada casilla de la tabla. La secuencia es la siguiente:
    // - Si no tiene color y caben más casillas rojas, se pone roja, si no, amarilla
    // - Si está roja se pone amarilla y se descuenta una casilla roja
    // - Si está amarilla se deja sin color
    
    $('.pref').click(function()
    {
        var id = $(this).attr('id');
        // Entraremos aquí si la casilla elegida no tiene color
        if ($(this).attr('class') == 'pref')
        {
            // Si no tiene color y caben más rojas, se pone roja
            if (contRojas < maxRojas)
            {
                preferencia(id, 1);
                $(this).attr('class', 'pref rojo');
                contRojas++;
            // Si no tiene color y no caben más rojas, se pone amarilla
            } else {
                preferencia(id, 2)
                $(this).attr('class', 'pref amarillo');                
            }
        }
        // Si tiene color rojo, se cambia a amarillo
        // y se descuenta una casilla roja
        else if ($(this).attr('class') == 'pref rojo')
        {
            preferencia(id, 2)
            $(this).attr('class', 'pref amarillo');
            contRojas--;
        }
        // Si no, es que está amarilla, y se deja sin color
        else
        {
            preferencia(id, 0);
            $(this).attr('class', 'pref');
        }
    });    

</script>