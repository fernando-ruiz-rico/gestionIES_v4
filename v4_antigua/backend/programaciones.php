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
    <h1>Edición de programaciones</h1>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') { ?>
        <?php include('includes/seleccion_departamento.php'); ?>
    <?php } ?>

    <?php if (isset($_SESSION['departamentoUsuario'])) { ?>
        <?php $departamentoId = (int)$_SESSION['departamentoUsuario']; ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="materia" class="form-label">Materia</label>
                <select class="form-control" name="materia" id="materia" onchange="cambiarMateria()">
                    <option value="0">--Selecciona una materia--</option>
                    <?php include('includes/cargar_materias_programaciones.php'); ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="apartado" class="form-label">Apartado</label>
                <select class="form-control" id="apartado" onchange="cambiarApartado()">
                    <option value="0">--Selecciona un apartado--</option>
                </select>
            </div>
        </div>
        
        <div class="row g-2 mb-4">
            <!-- Sólo el admin y los jefes de departamento pueden editar contenidos por defecto de temas -->
            <?php if ($permisos) { ?>
                <div class="col-md">
                    <button class="btn btn-light w-100" onclick="contenidoDefectoTemas()"> 
                        <i class="bi bi-database-down"></i> Cont. defecto Unidades
                    </button>

                </div>
            <?php } ?>

            <!-- Sólo en época de edición de programaciones se permite editar los temas -->
            <!-- La variable $programacionesActivadas se crea en el include comprobar_activaciones, cargado desde abecera.php -->
            <?php if ($programacionesActivadas || $permisos) { ?>
                <div class="col-md">
                    <form name="temas" id="temas" action="temas.php" method="get" target="_blank">
                        <input type="hidden" name="idMateria" value="">
                        <button type="submit" class="btn btn-light w-100">
                            <i class="bi bi-list-ul"></i> Unidades
                        </button>
                    </form>
                </div>
            <?php } ?>

            <div class="col-md">
                <button class="btn btn-light w-100" onclick="generarPDFUnidades()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF de Unidades
                </button>
            </div>

            <!-- Desactivado temporalmente hasta tenerlo acabado
            <div class="col-md">
                <button class="btn btn-light w-100" onclick="vistaPreviaProgramacion()">
                    <i class="bi bi-eye"></i> Previsualizar
                </button>
            </div>
            -->

            <div class="col-md">
                <button class="btn btn-light w-100" onclick="generarPDFApartado()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF de Apartado
                </button>
            </div>

            <div class="col-md">
                <button class="btn btn-light w-100" onclick="generarPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF Completo
                </button>
            </div>

            <!-- Sólo el admin y los jefes de departamento pueden importar otras programaciones -->
            <?php if ($permisos) { ?>
                <div class="col-md">
                    <button class="btn btn-primary w-100" onclick="importarProgramacion()">
                        <i class="bi bi-box-arrow-in-down"></i> Importar
                    </button>
                </div>
            <?php } ?>
        </div>

        <!-- La variable $programacionesActivadas se crea en el include comprobar_activaciones, cargado desde cabecera.php -->
        <?php if ($programacionesActivadas || $permisos) { ?>
            <div id="edicionapartado" class="mt-4">
                <form name="formprogramacion" id="formprogramacion" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idMateria" value="">
                    <input type="hidden" name="idApartado" id="idApartado" value="">
                    <textarea name="texto" class="progeditar" id="texto"></textarea>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-light">
                            <i class="bi bi-floppy"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <div id="mensajeapartadoautomatico" class="mt-4" style="display: none;">
                <p><em>El apartado seleccionado se genera automáticamente a partir de la información introducida en secciones como las Unidades de Programación o los Resultados de Aprendizaje. Para modificarlo, realiza los cambios en esas secciones. Puedes visualizarlo haciendo clic en el botón "PDF de Apartado".</em></p>
            </div>

        <?php } ?>

        <!-- Script para pasar el departamento al JavaScript -->
        <script>
            const selDepartamento = <?= json_encode($departamentoId); ?>;
        </script>

        <?php include('modales/importar_programacion.php'); ?>

    <?php } ?>
</div>

<script>
    const TIPO_APARTADO_TEMAS = <?php echo json_encode(TIPO_APARTADO_TEMAS); ?>;
</script>
<script src="js/programaciones.js?v=4"></script>

<?php
require_once('includes/database2.php');
require_once('includes/pie.php');
?>