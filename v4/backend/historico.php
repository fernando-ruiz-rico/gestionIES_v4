<?php
// Vista principal para ver el histórico de desideratas
include('includes/cabecera.php');    
?>

<div class="panelcentral">

    <h1>Histórico de selecciones</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
            $escenarioActual = 0;
            if(!empty($_REQUEST['idEscenario']))
                $escenarioActual = $_REQUEST['idEscenario'];
    ?>

    <div>
        <strong>Escenario: </strong>
        <select name="escenario" class="form-control" id="escenarioHistorico" onChange="seleccionarEscenarioHistorico()">
            <option value="-1" selected>--Selecciona un escenario--</option>
        <?php
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE id IN (SELECT idEscenario FROM departamentos_escenarios WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . ") ORDER BY nombre");
            while ($fila = mysqli_fetch_assoc($result))
            {
                if($fila['id'] == $escenarioActual)
                    echo '<option value="' . $fila['id'] . '" selected>' . $fila['nombre'] . '</option>';
                else
                    echo '<option value="' . $fila['id'] . '">' . $fila['nombre'] . '</option>';
            }
            mysqli_free_result($result);
            include('includes/database2.php');
        ?>
        </select>
    </div>

    <div id="historico">
    </div>

    <script src="js/historico.js"></script>	

    <?php
    }
    ?>

<?php    
    include('includes/pie.php');
?>
