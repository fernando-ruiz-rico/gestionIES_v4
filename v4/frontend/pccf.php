<?php
// Página principal de gestión de contenidos de las programaciones
require_once('includes/cabecera.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/database.php');

// Determinar permisos: solo los administradores tienen permisos completos
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

?>

<div class="panelcentral">
    <h1 class="mb-4">Proyecto Curricular de Ciclo Formativo (PCCF)</h1>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') { ?>
        <?php include('includes/seleccion_departamento.php'); ?>
    <?php } ?>

    <?php if (isset($_SESSION['departamentoUsuario'])) { ?>
        <?php $departamentoId = (int)$_SESSION['departamentoUsuario']; ?>

        <div class="row mb-3">
            <div class="col-md">
                <label for="ciclo" class="form-label">Ciclo formativo</label>
                <select class="form-control" name="ciclo" id="ciclo" onchange="cambiarCiclo()">
                    <option value="0">--Selecciona un ciclo formativo--</option>
                    <?php include('includes/cargar_ciclos.php'); ?>
                </select>
            </div>

            <?php if ($permisos) { ?>
                <div class="col-md">
                    <label for="apartado" class="form-label">Apartado</label>
                    <select class="form-control" id="apartado" onchange="cambiarApartado()">
                        <option value="0">--Selecciona un apartado--</option>
                        <?php include('includes/cargar_apartados_pccf.php'); ?>
                    </select>
                </div>
            <?php } ?>
        </div>
        
        <div class="row g-2 mb-4">
            
            <?php if ($permisos) { ?>
                <div class="col-md">
                    <button class="btn btn-light w-100" id="generarpdfapartado" onclick="generarPDFApartado()">
                        <img src="img/pdf.png" alt="PDF por apartado"> Generar PDF de Apartado
                    </button>
                </div>
            <?php } ?>

            <div class="col-md">
                <button class="btn btn-light w-100" id="generarpdfcompleto" onclick="generarPDF()">
                    <img src="img/pdf.png" alt="PDF completo"> Generar PDF
                </button>
            </div>
        </div>

        <!-- La variable $programacionesActivadas se crea en el include comprobar_activaciones, cargado desde cabecera.php -->
        <?php if ($programacionesActivadas || $permisos) { ?>
            <div id="edicionapartado" class="mt-4">
                <form id="formpccf" name="formpccf" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idCiclo" id="idCiclo" value="">
                    <input type="hidden" name="idApartado" id="idApartado" value="">
                    <textarea name="texto" class="progeditar" id="texto"></textarea>
                    <div class="text-center mt-3">
                        <button class="btn btn-light" type="submit"><img src="img/save.png"> 
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <div id="mensajeapartadoautomatico" class="mt-4" style="display: none;">
                <p><em>El apartado seleccionado se genera automáticamente a partir de la información introducida en la base de datos, o en otras secciones de la aplicación. Puedes visualizarlo haciendo clic en el botón "PDF de Apartado".</em></p>
            </div>
        <?php } ?>

        <!-- Script para pasar el departamento al JavaScript -->
        <script type="text/javascript">
            let selDepartamento = <?= $_SESSION['departamentoUsuario'] ?>;
        </script>

    <?php } ?>
</div>

<script src="js/pccf.js?v=1"></script>

<?php
require_once('includes/database2.php');
require_once('includes/pie.php');
?>