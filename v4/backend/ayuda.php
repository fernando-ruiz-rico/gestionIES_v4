<?php

// Página para visualización de ayuda

include('includes/cabecera.php');
include('lib/php/parsedown/Parsedown.php');

// Recogemos el tipo de ayuda. Si llega parámetro "admin" es para administrador, de lo contrario para profesor
$documento = '../docs/Manual_Profe.md';
if(!empty($_REQUEST['admin']))
{
    $documento = '../docs/Manual_Admin.md';
}

$contenido = file_get_contents($documento);
$parser = new Parsedown();

?>

<div class="panelcentral">

    <?php
        echo $parser->text($contenido);
    ?>

</div>

<?php
include('includes/pie.php');
?>
