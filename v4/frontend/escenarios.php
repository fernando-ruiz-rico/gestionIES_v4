<?php

// Vista principal para la gestión de escenarios de desideratas
$roles = ['admin', 'jefeDepartamento'];
include('includes/cabecera.php');

// Sólo jefes de departamento y administradores pueden gestionar escenarios
if($permisos)
{
?>

<div class="panelcentral">

    <h1>Escenarios posibles para desideratas</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <p><em>Elige un posible escenario de la lista para editarlo, o crea nuevos con el botón "Nuevo escenario"</em></p>
    <div id="escenariosdesid"></div>
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoEscenario()"><i class="bi bi-plus"></i> Nuevo escenario</button></div>
</div>

<?php
    include('modales/escenarios.php');

// if de si hay departamento asignado
}
// if de si hay permisos de edición
}
?>

<script src="js/escenarios.js"></script>	

<?php
    include('includes/pie.php');
?>