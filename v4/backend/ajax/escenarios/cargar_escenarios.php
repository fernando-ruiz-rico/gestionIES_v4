<?php

// Devuelve un listado HTML con los datos de los escenarios de la base de datos

@session_start();

if(isset($_SESSION['departamentoUsuario']))
{
    include('../../includes/database.php');

    // Mostramos únicamente los escenarios vinculados al departamento del usuario actual
    $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE id IN (SELECT idEscenario FROM departamentos_escenarios WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . ') ORDER BY nombre');

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $actual = $fila['actual'];
        $activoDesideratas = $fila['activo_desideratas'];
        $modoRueda = $fila['modo_rueda'];
        $textoActual = $actual?"Escenario actualmente en vigor":"Escenario antiguo o no en vigor";
        $textoDesideratas = $activoDesideratas?"Escenario activo para desideratas":"Escenario no elegible en desideratas";
        $textoRueda = $modoRueda?"Escenario en modo rueda":"Modo rueda desactivado";
        $iconoActivo = $activoDesideratas?"bi-unlock":"bi-lock";
        
        echo '<div class="listado claro izquierda">';
        echo '<div class="izquierda">'. $nombre . '</div>';
        // Botones para hacer acciones sobre el escenario
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarEscenario(' . $id . ",'" . $nombre . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarEscenarioModal(' . $id . ')" title="Editar nombre del escenario"><i class="bi bi-pencil-square"></i></button><button class="btn ' . ($actual?'btn-success':'btn-light') . '" onclick="marcarEscenarioActual(' . $id . ", '" . ($actual?"si":"no") . "'" . ')"><i class="bi bi-list-check"></i></button><button class="btn ' . ($activoDesideratas?'btn-light':'btn-danger') . '" onclick="marcarEscenarioActivoDesideratas(' . $id . ", '" . ($activoDesideratas?"si":"no") . "'" . ')"><i class="bi ' . $iconoActivo . '"></i></button><button class="btn btn-light" onclick="duplicarEscenario(' . $id . ')"><i class="bi bi-copy"></i></button><button class="btn ' . ($modoRueda?'btn-success':'btn-light') . '" onclick="modoRueda(' . $id . ", '" . ($modoRueda?"si":"no") . "'" . ')"><i class="bi bi-sliders"></i></button></div>';
        echo '</div>';
    }

    mysqli_free_result($result);

    include ('../../includes/database2.php');
}
?>
