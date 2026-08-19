<!-- Vista principal para la gestión de temas o unidades -->

<?php
require_once('includes/cabecera.php');
require_once('includes/database.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('modales/nuevo_tema.php');

// Miramos si la materia en cuestión corresponde a un ciclo formativo o no
// Lo usaremos para mostrar/ocultar/personalizar ciertos apartados a rellenar

$idMateria = (int)$_REQUEST['idMateria'];
$datosMateria = obtenerDatosMateria($idMateria);

// Devuelve un listado HTML de los temas/unidades asociados a una materia
function mostrarTemasPorMateria($idMateria)
{
    $sql = "SELECT id, orden, titulo, peso_evaluacion, horas FROM temas WHERE idMateria = $idMateria ORDER BY orden";
    $temas = consultarBaseDeDatos($sql);

    $horas_materia = obtenerHorasAnualesPorMateria($idMateria);

    $suma_pesos = 0;
    $suma_horas = 0;

    foreach ($temas as $tema) {
        $idTema = (int)$tema['id'];
        $orden = (int)$tema['orden'];
        $titulo = htmlspecialchars($tema['titulo'], ENT_QUOTES, 'UTF-8');
        $horas_tema = (int)$tema['horas'];
        $peso_evaluacion = (int)$tema['peso_evaluacion'];

        $suma_pesos += $peso_evaluacion;
        $suma_horas += $horas_tema;

        $titulo_js = addslashes($titulo);

        echo "<div class='listado claro izquierda'>";
        echo "<div class='izquierda'>$orden. $titulo</div>";
        echo "<div class='derecha'>";
        echo "<span class='me-2'>$peso_evaluacion% ({$horas_tema}h)</span>";
        echo "<button class='btn btn-light' onclick='borrarTema($idTema, \"$titulo_js\")'><img src='img/delete.png'></button>";
        echo "<a href='editar_tema.php?idMateria=$idMateria&idTema=$idTema' class='btn btn-light'><img src='img/edit.png'></a>";
        echo "</div>";
        echo "</div>";
    }

    if ($suma_pesos > 0 || $suma_horas > 0) {
        $error_pesos = ($suma_pesos != 100);
        $error_horas = ($suma_horas != $horas_materia);

        $color_pesos = $error_pesos ? 'text-danger' : 'text-success';
        $color_horas = $error_horas ? 'text-danger' : 'text-success';

        echo "<div class='listado claro izquierda text-center fw-bold'>";
        echo "Total: <span class='me-2 $color_pesos'>$suma_pesos %</span>";
        echo " (<span class='$color_horas'>$suma_horas / $horas_materia horas</span>)";
        if ($error_pesos || $error_horas ) {
            echo "<br><span class='fst-italic fs-6 text-danger'>";
            if ($error_pesos) {
                echo "El porcentaje debe ser 100%. ";
            }
            if ($error_horas) {
                echo "El total de horas debe coincidir con las horas anuales de la materia.";
            }
            echo "</span>";
        }
        else {
            echo "<img class='ms-2' src='img/thumb_up.png'>";
        }
        echo "</div>";
    }
}
?>

<div class="panelcentral">

    <h1>Unidades de programación</h1>
    <h2><?= $datosMateria['materia'] ?> (<?= $datosMateria['curso'] ?>)</h2>

    <div id="listatemas"><?= mostrarTemasPorMateria($idMateria) ?></div>
    <div class="text-center"><button class="btn btn-light" onclick="nuevoTema()"><i class="bi bi-plus-circle"></i> Nueva Unidad</button></div>
</div>

<script src="js/temas.js?v=8"></script>

<?php
    require_once('includes/database2.php');
    require_once('includes/pie.php');
?>
