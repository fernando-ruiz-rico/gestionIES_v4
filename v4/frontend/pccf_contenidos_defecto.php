<?php
// Página principal de gestión de contenidos por defecto en apartados de los PCCF
$roles = ['admin', 'jefeDepartamento'];
include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Contenidos por defecto para los PCCF</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
    ?>

    <p><em>Algunos contenidos de los PCCF se prestan a ser comunes para varios ciclos. En esta sección podemos editarlos entre todos y mantenerlos de forma que se puedan reaprovechar. NOTA: no todos los contenidos están disponibles en esta sección, sólo los susceptibles de ser compartidos entre ciclos. En cualquier caso, si se rellena el contenido en un ciclo dado, prevalecerá dicho contenido sobre el que haya por defecto.</em></p>
           
    <select class="form-control" name="seleccionApartado" id="seleccionApartado" onchange="cambiarApartado()">
        <option value="0">--Selecciona un apartado--</option>
        <?php
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM apartados_pccf ORDER BY orden");
            $cont = 0;
            $cont2 = 0;

            // Recorremos todos los apartados para mantener la numeración original, pero sólo 
            // mostramos en la lista los que admitan contenido por defecto
            while($fila = mysqli_fetch_assoc($result))
            {
                $id = $fila['id'];
                $titulo = $fila['titulo'];
                $subapartado = $fila['subapartado'];
                $admite = $fila['contenido_defecto'];
                $tipo = $fila['tipo'];
                if (!$subapartado)
                {
                    $cont++;
                    $cont2 = 0;
                    if ($admite && $tipo == 0)
                        echo '<option value="' . $id . '">' . "$cont. $titulo" . '</option>';
                } else {
                    $cont2++;
                    if ($admite && $tipo == 0)
                        echo '<option value="' . $id . '">' . "$cont.$cont2. $titulo" . '</option>';
                }
            }

            include('includes/database2.php');
        ?>
    </select>                
        
    <div id="edicionapartado">
        <form id="formpccfdefault" name="formpccfdefault" method="post" enctype="multipart/form-data">
            <input type="hidden" name="idDepartamento" id="idDepartamento" value="" />
            <input type="hidden" name="idApartado" id="idApartado" value="" />
            <textarea name="texto" class="pccfeditar" id="texto"></textarea>
            <div style="text-align:center"><button class="btn btn-light" type="submit"><img src="img/save.png" /> Guardar cambios</button></div>
        </form>
    </div>

    <script type="text/javascript">
        var selDepartamento = <?= $_SESSION['departamentoUsuario'] ?>;
    </script>

    <?php
    // if de comprobación de departamento
    }
    ?>

    <script src="js/pccf_contenidos_defecto.js"></script>	

</div>

<?php
include('includes/pie.php');
?>