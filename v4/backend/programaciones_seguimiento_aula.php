<?php
require_once('includes/cabecera.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/database.php');

// Permisos: Admin o Jefe de Departamento
$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin');
$permisos = ($esAdmin || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'jefeDepartamento'));
$cursoActual = cursoActual();
?>

<div class="panelcentral">
    <h1>Seguimiento de programaciones</h1>

    <?php
    echo "<p><strong>Curso actual</strong>: $cursoActual</p>";

    if ($permisos) {
        //include('includes/seleccion_departamento.php');
        include('includes/seleccion_profesor.php');
    }

    if (isset($_SESSION['departamentoUsuario'])) {
        
        // Selector de Curso
        /*if ($permisos) {
            echo '<select class="form-control mb-3" name="cursoSeguimiento" id="cursoSeguimiento">';
            echo '<option value="0">--Selecciona curso académico--</option>';
            include('includes/cargar_cursos_seguimiento.php');
            echo '</select>';
        } else {
            echo '<p><strong>Curso actual</strong>: ' . $cursoActual . '</p>';
        }*/

        // Determinación de dpto. y el ID Profesor
        $idDepartamento = $_SESSION['departamentoUsuario'];
        $idProfesor = ($permisos && isset($_REQUEST['idProfesor'])) ? $_REQUEST['idProfesor'] : (isset($_SESSION['idUsuario']) ? $_SESSION['idUsuario'] : null);

        if ($idProfesor !== null) { ?>
            <form name="formSeguimientoAula" id="formSeguimientoAula" method="post">
                <input type="hidden" name="idDepartamento" id="idDepartamento" value="<?php echo $idDepartamento; ?>">
                <input type="hidden" name="idProfesor" id="idProfesor" value="<?php echo $idProfesor; ?>">
                <input type="hidden" name="curso" id="curso" value="<?php echo $cursoActual; ?>">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Evaluación</label>
                        <select class="form-control" name="idEvaluacion" id="idEvaluacion" onchange="cambiarEvaluacion()">
                            <option value="0">--Selecciona evaluación--</option>
                            <?php include('includes/cargar_evaluaciones.php'); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Materia</label>
                        <select class="form-control" name="idMateria" id="idMateria" onchange="cambiarMateria()">
                            <option value="0">--Selecciona una materia--</option>
                            <?php include("includes/cargar_materias_programaciones.php"); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grupo</label>
                        <select class="form-control" name="idGrupo" id="idGrupo" onchange="cambiarGrupo()">
                            <option value="0">--Selecciona un grupo--</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <button class="btn btn-light w-100" type="button" onclick="generarPDFSeguimientoAula('FP')">
                            <img src="img/pdf.png"> PDF seguimiento Ciclos Formativos
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-light w-100" type="button" onclick="generarPDFSeguimientoAula('ESO/BACH')">
                            <img src="img/pdf.png"> PDF seguimiento ESO/BACH
                        </button>
                    </div>
                </div>

                <div id="edicionseguimientoaula" style="display:none;">
                    <div class="mt-4">
                        <label class="control-label mb-2">SEGUIMIENTO DE LA PROGRAMACIÓN, con respecto a la temporalización que figura en las Propuestas Pedagógicas:</label>
                        <textarea class="seguimientoeditar" id="temporalizacion" name="temporalizacion"></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="control-label mb-2">VALORACIÓN DE LOS RESULTADOS ACADÉMICOS, detallando cumplimiento de programación, incidencia sobre la convivencia del grupo y resultados académicos:</label>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text">Aprobados:</span>
                                    <input type="number" class="form-control" id="num_aprobados" name="num_aprobados" value="0" min="0" max="99" oninput="calcularTotalAlumnos()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text">Suspensos:</span>
                                    <input type="number" class="form-control" id="num_suspensos" name="num_suspensos" value="0" min="0" max="99" oninput="calcularTotalAlumnos()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text">Otros:</span>
                                    <input type="number" class="form-control" id="num_otros" name="num_otros" value="0" min="0" max="99" oninput="calcularTotalAlumnos()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text">Total:</span>
                                    <input type="number" class="form-control" id="alumnostotal" name="alumnostotal" value="0" disabled readonly>
                                </div>
                            </div>
                        </div>
                        <textarea class="seguimientoeditar" id="resultados" name="resultados"></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="control-label mb-2">INCLUSIÓN DEL ALUMNADO (si procede), detallando la valoración de los resultados de alumnado a quien se le ha aplicado algún tipo de respuesta educativa:</label>
                        <textarea class="seguimientoeditar" id="inclusion" name="inclusion"></textarea>
                    </div>

                    <div class="text-center mt-4 mb-2">
                        <button type="submit" class="btn btn-light btn-lg">
                            <img src="img/save.png" alt="Guardar"> Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
    <?php 
        } else {
            echo '<p class="text-danger">No se ha podido determinar el profesor/a para cargar las programaciones.</p>';
        }
    }    
    ?>
</div>

<script src="js/programaciones_seguimiento_aula.js?v=2"></script>

<?php
require_once('includes/database2.php');
require_once('includes/pie.php');
?>