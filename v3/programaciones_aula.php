<?php
// Página principal de gestión de contenidos de las programaciones de aula
require_once('includes/cabecera.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/database.php');

// Determinar permisos: solo los administradores tienen permisos completos
// De momento, los jefes de departamento también tienen permisos completos
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'jefeDepartamento');

?>

<div class="panelcentral">
    <h1>Programaciones de aula</h1>

    <?php 
        if ($permisos) {
            include('includes/seleccion_profesor.php');
        }

        if (($permisos && isset($_REQUEST['idProfesor'])) || (!$permisos && isset($_SESSION['idUsuario']))) { 
            if ($permisos && isset($_REQUEST['idProfesor']))
                $idProfesor = $_REQUEST['idProfesor'];
            else
                $idProfesor = $_SESSION['idUsuario'];
    ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="materia" class="form-label">Materia</label>
                <select class="form-control" name="materia" id="materia" onchange="cambiarMateria()">
                    <option value="0">--Selecciona una materia--</option>
                    <?php 
                        $resultado = consultarBaseDeDatos("SELECT DISTINCT cursos.nombre AS nomCurso, materias.nombre as nomMateria, materias.id FROM cursos, materias, seleccion, escenarios_desideratas WHERE escenarios_desideratas.id = seleccion.idEscenario AND cursos.id = materias.idCurso AND materias.id = seleccion.idMateria AND seleccion.idProfesor = $idProfesor AND materias.tiene_programacion = TRUE AND escenarios_desideratas.actual = TRUE ORDER BY nomMateria");
                        foreach($resultado as $fila)
                        {
                            echo '<option value="' . $fila['id'] . '">' . $fila['nomMateria'] . ' (' . $fila['nomCurso'] . ')</option>';
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="grupo" class="form-label">Grupo</label>
                <select class="form-control" id="grupo" onchange="cambiarGrupo()">
                    <option value="0">--Selecciona un grupo--</option>
                </select>
            </div>
        </div>
        <!-- De momento comentado el código para seleccionar unidad y generar PDF de la unidad
        <div class="row mb-3">
            <div class="col-md-10">
                <label for="tema" class="form-label">Unidad</label>
                <select class="form-control" id="tema" onchange="cambiarTema()">
                    <option value="0">--Selecciona un tema o unidad--</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-center justify-content-center">
                <button class="btn btn-light w-100" onclick="generarPDF()">
                    <img src="img/pdf.png" alt="PDF completo"> PDF Programación de aula
                </button>
            </div>
        </div>
        -->

        <div class="row mb-3">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <button class="btn btn-light w-100" onclick="generarPDFSeparataCE()">
                    <img src="img/pdf.png" alt="PDF completo"> PDF Separata CE
                </button>
            </div>

            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <button class="btn btn-light w-100" onclick="generarPDF()">
                    <img src="img/pdf.png" alt="PDF completo"> PDF Programación de aula
                </button>
            </div>
        </div>

        <p>
        <div id="ediciontema" class="mt-4">
            <form name="formprogramacionaula" id="formprogramacionaula" method="post" enctype="multipart/form-data">
                <input type="hidden" name="idMateria" id="idMateria" value="">
                <input type="hidden" name="idGrupo" id="idGrupo" value="">
                <input type="hidden" name="idProfesor" id="idProfesor" value="<?= $idProfesor ?>">
                <input type="hidden" name="idTema" id="idTema" value="0">
                <label for="texto" class="control-label mb-2">Texto de introducción (opcional). Si se deja vacío, se utilizará un texto por defecto:</label>
                <textarea name="texto" class="progeditar" id="texto"></textarea>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-light">
                        <img src="img/save.png" alt="Guardar"> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
             
        <!-- Script para pasar el profesor al JavaScript -->
        <script>
            const selProfesor = <?= json_encode($idProfesor); ?>;
        </script>

    <?php } ?>
</div>

<script src="js/programaciones_aula.js?v=2"></script>

<?php
require_once('includes/database2.php');
require_once('includes/pie.php');
?>