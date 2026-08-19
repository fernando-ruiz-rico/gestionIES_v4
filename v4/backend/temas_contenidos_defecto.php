<?php
// Página principal de gestión de contenidos por defecto en temas o unidades
$roles = ['admin', 'jefeDepartamento'];
include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Contenidos por defecto para las unidades o temas</h1>

    <?php
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin')
        {
            // Los admins eligen el departamento con el que trabajar en esta sección
            include('includes/seleccion_departamento.php');
        }
        if (isset($_SESSION['departamentoUsuario']))
        {
            // Cargamos los contenidos del tema
            include('includes/database.php');
            $result = mysqli_query($db, "SELECT * FROM contenidos_defecto_temas WHERE idDepartamento = " . $_SESSION['departamentoUsuario']);
            $contexto = "";
            $recursos = "";
            $metodologia = "";
            $adaptaciones = "";
            if($fila = mysqli_fetch_assoc($result))
            {
                $contexto = $fila['contexto'];
                $recursos = $fila['recursos'];
                $metodologia = $fila['metodologia'];
                $adaptaciones = $fila['adaptaciones'];
            }

            include('includes/database2.php');
    ?>

    <p><em>Algunos contenidos de las unidades se prestan a ser comunes para varias. En esta sección podemos editarlos y mantenerlos de forma que se puedan reaprovechar. NOTA: no todos los apartados están disponibles en esta sección, sólo los susceptibles de ser compartidos entre unidades. En cualquier caso, si un profesor desactiva esta opción para alguna de sus unidades prevalecerá el contenido propio de esa unidad.</em></p>        
        
    <div id="edicionapartado">
        <form id="formtemadefault" name="formtemadefault" method="post" enctype="multipart/form-data">
            <input type="hidden" name="idDepartamento" id="idDepartamento" value="" />

            <!-- Pestañas con los apartados editables -->
            <ul class="nav nav-tabs" id="tabsTema" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab_contexto" data-bs-toggle="tab" data-bs-target="#seccion_contexto" type="button" role="tab" aria-controls="seccion_contexto" aria-selected="true">
                        Contexto
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab_recursos" data-bs-toggle="tab" data-bs-target="#seccion_recursos" type="button" role="tab" aria-controls="seccion_recursos" aria-selected="false">
                        Recursos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab_metodologia" data-bs-toggle="tab" data-bs-target="#seccion_metodologia" type="button" role="tab" aria-controls="seccion_metodologia" aria-selected="false">
                        Metodología
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab_adaptaciones" data-bs-toggle="tab" data-bs-target="#seccion_adaptaciones" type="button" role="tab" aria-controls="seccion_adaptaciones" aria-selected="false">
                        Adaptaciones
                    </button>
                </li>
            </ul>
            <div class="tab-content mt-3 mb-3" id="contenidoTabs">
                <div class="tab-pane fade show active" id="seccion_contexto" role="tabpanel" aria-labelledby="tab_contexto">
                    <label class="control-label" for="contexto">Contexto</label>
                    <textarea name="contexto" class="datostema" id="contexto"><?= $contexto; ?></textarea>
                </div>
                <div class="tab-pane fade" id="seccion_recursos" role="tabpanel" aria-labelledby="tab_recursos">
                    <label class="control-label" for="recursos">Recursos</label>
                    <textarea name="recursos" class="datostema" id="recursos"><?= $recursos; ?></textarea>
                </div>
                <div class="tab-pane fade" id="seccion_metodologia" role="tabpanel" aria-labelledby="tab_metodologia">
                    <label class="control-label" for="metodologia">Metodología</label>
                    <textarea name="metodologia" class="datostema" id="metodologia"><?= $metodologia; ?></textarea>
                </div>
                <div class="tab-pane fade" id="seccion_adaptaciones" role="tabpanel" aria-labelledby="tab_adaptaciones">
                    <label class="control-label" for="adaptaciones">Adaptaciones</label>
                    <textarea name="adaptaciones" class="datostema" id="adaptaciones"><?= $adaptaciones; ?></textarea>
                </div>
            </div>

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

    <script src="js/temas_contenidos_defecto.js"></script>	

</div>

<?php
include('includes/pie.php');
?>