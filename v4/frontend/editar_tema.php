<!-- Ventana modal para editar los datos de un tema/unidad de una programación -->

<?php
require_once('includes/cabecera.php');
require_once('includes/database.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');

$idTema = (int)$_REQUEST['idTema'];
$idMateria = (int)$_REQUEST['idMateria'];
$tema = obtenerDatosTema($idTema, $idMateria);

$idCiclo = obtenerIdCicloPorMateria($idMateria);
$horas_materia = obtenerHorasAnualesPorMateria($idMateria);

// -------------------------------
// Genera los checkboxes de competencias para una materia y ciclo dados
// -------------------------------
function generarCheckboxesCompetenciasMateria($idMateria, $idCiclo)
{
    $html = '<p>';
    if ($idCiclo > 0) {
        $html .= 'Competencias profesionales (negro) y para la empleabilidad (<span class="text-darkgreen">verde</span>)';
    } else {
        $html .= 'Competencias clave';
    }
    $html .= '</p>';

    $competencias = array();
    $idsYaAgregados = array();

    // Tipo 1: asignadas a la materia
    $sqlTipo1 = "
        SELECT cc.*
        FROM competencias_ciclos cc
        INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
        WHERE cm.idMateria = {$idMateria} AND cc.tipo = 1
        ORDER BY cc.orden";
    $resTipo1 = consultarBaseDeDatos($sqlTipo1);

    foreach ($resTipo1 as $fila) {
        $competencias[] = $fila;
        $idsYaAgregados[(int)$fila['id']] = true;
    }

    // Tipo 2: del ciclo (siempre)
    $sqlTipo2 = "
        SELECT *
        FROM competencias_ciclos
        WHERE idCiclo = {$idCiclo} AND tipo = 2
        ORDER BY orden";
    $resTipo2 = consultarBaseDeDatos($sqlTipo2);

    foreach ($resTipo2 as $fila) {
        $id = (int)$fila['id'];
        if (!isset($idsYaAgregados[$id])) {
            $competencias[] = $fila;
            $idsYaAgregados[$id] = true;
        }
    }

    if (empty($competencias)) {
        return '<p>No hay competencias de tipo 1 en la materia ni de tipo 2 en el ciclo.</p>';
    }

    usort($competencias, function($a, $b) {
        if ($a['tipo'] == $b['tipo']) {
            return ($a['orden'] < $b['orden']) ? -1 : 1;
        }
        return ($a['tipo'] < $b['tipo']) ? -1 : 1;
    });

    foreach ($competencias as $fila) {
        $id = (int)$fila['id'];
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        $claseColor = ((int)$fila['tipo'] === 1) ? 'text-body' : 'text-darkgreen';

        $html .= "
            <div class='form-check mb-0'>
                <input class='form-check-input me-1 check_com' type='checkbox' name='com[]' id='com{$id}' value='{$id}'>
                <label class='form-check-label {$claseColor} d-inline-block text-truncate w-100' title='{$texto}' for='com{$id}'>{$codigo}. {$texto}</label>
            </div>";
    }

    return $html;
}

?>

<div class="m-5 p-3 bg-white border rounded-3 shadow">

    <h3>Formulario de edición tema/unidad</h3>

    <!-- El id "formeditar" se usa para enviar el formulario por jQuery -->
    <!-- Todos los datos del formulario se rellenan por AJAX para el ciclo seleccionado -->
    <form id="formeditar" name="formeditar" method="post" enctype="multipart/form-data">
        <!-- Id de la unidad o tema -->
        <input type="hidden" name="idTema" id="idTema" value="<?= $tema['id'] ?>">
        <!-- Primeras filas con campos básicos -->
        <div class="row">
            <div class="col-md-2">
                <label class="control-label" for="orden">Número del tema</label>
                <input class="form-control" type="number" name="orden" id="orden" min="1" value="<?= $tema['orden'] ?>" required>
            </div>
            <div class="col-md-2">
                <label class="control-label" for="horas">Horas de la unidad</label>
                <input class="form-control" type="number" name="horas" id="horas" min="1" value="<?= $tema['horas'] ?>" required>
            </div>
            <div class="col-md-2">
                <label class="control-label" for="horas_anuales">Horas anuales</label>
                <input class="form-control" type="number" name="horas_anuales" id="horas_anuales" disabled value="<?= $horas_materia; ?>">
            </div>
            <div class="col-md-3">
                <label class="control-label" for="trimestre">Trimestre</label>
                <select class="form-control" name="trimestre" id="trimestre" required>
                    <option value="">-- Selecciona un trimestre --</option>
                    <option value="1" <?= $tema['trimestre'] == 1 ? 'selected' : '' ?>>1º Trimestre</option>
                    <option value="2" <?= $tema['trimestre'] == 2 ? 'selected' : '' ?>>2º Trimestre</option>
                    <option value="3" <?= $tema['trimestre'] == 3 ? 'selected' : '' ?>>3º Trimestre</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="control-label" for="peso_evaluacion">% Peso evaluación anual</label>
                <input class="form-control" type="number" name="peso_evaluacion" id="peso_evaluacion" min="1" max="100" value="<?= $tema['peso_evaluacion'] ?>" required>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2">
                <label class="control-label" for="titulo">Título</label>
            </div>
            <div class="col-md-10">
                <input class="form-control" type="text" name="titulo" id="titulo" value="<?= $tema['titulo'] ?>" required>
            </div>
        </div>
        <!-- Pestañas con campos más complejos -->
        <ul class="nav nav-tabs mt-2" id="tabsTema" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab_descripcion" data-bs-toggle="tab" data-bs-target="#seccion_descripcion" type="button" role="tab" aria-controls="seccion_descripcion" aria-selected="true">
                    Descripción
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_justificacion" data-bs-toggle="tab" data-bs-target="#seccion_justificacion" type="button" role="tab" aria-controls="seccion_justificacion" aria-selected="false">
                    Justificación
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_contexto" data-bs-toggle="tab" data-bs-target="#seccion_contexto" type="button" role="tab" aria-controls="seccion_contexto" aria-selected="false">
                    Contexto
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_contenidos" data-bs-toggle="tab" data-bs-target="#seccion_contenidos" type="button" role="tab" aria-controls="seccion_contenidos" aria-selected="false">
                    <?= $idCiclo > 0 ? "Contenidos" : "Saberes básicos"; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_secuenciacion" data-bs-toggle="tab" data-bs-target="#seccion_secuenciacion" type="button" role="tab" aria-controls="seccion_secuenciacion" aria-selected="false">
                    Secuenciación/Actividades
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_recursos" data-bs-toggle="tab" data-bs-target="#seccion_recursos" type="button" role="tab" aria-controls="seccion_recursos" aria-selected="false">
                    Recursos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_evaluacion" data-bs-toggle="tab" data-bs-target="#seccion_evaluacion" type="button" role="tab" aria-controls="seccion_evaluacion" aria-selected="false">
                    Evaluación
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_ra_ce" data-bs-toggle="tab" data-bs-target="#seccion_ra_ce" type="button" role="tab" aria-controls="seccion_ra_ce" aria-selected="false">
                    <?= ($idCiclo > 0) ? "RA/CE" : "CE/CR"; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_competencias" data-bs-toggle="tab" data-bs-target="#seccion_competencias" type="button" role="tab" aria-controls="seccion_competencias" aria-selected="false">
                    <?= ($idCiclo > 0) ? "Competencias" : "Competencias clave"; ?>
                </button>
            </li>
        </ul>
        <div class="tab-content mt-3 mb-3" id="contenidoTabs">
            <div class="tab-pane fade show active" id="seccion_descripcion" role="tabpanel" aria-labelledby="tab_descripcion">
                <label class="control-label" for="descripcion">Descripción</label>
                <textarea name="descripcion" class="datostema" id="descripcion"><?= $tema['descripcion'] ?></textarea>
            </div>
            <div class="tab-pane fade" id="seccion_justificacion" role="tabpanel" aria-labelledby="tab_justificacion">
                <label class="control-label" for="justificacion">Justificación</label>
                <textarea name="justificacion" class="datostema" id="justificacion"><?= $tema['justificacion'] ?></textarea>
            </div>
            <div class="tab-pane fade" id="seccion_contexto" role="tabpanel" aria-labelledby="tab_contexto">
                <label class="control-label" for="contexto">Contexto</label>
                <textarea name="contexto" class="datostema" id="contexto"><?= $tema['contexto'] ?></textarea>
                <input type="checkbox" id="contexto_defecto" name="contexto_defecto" <?= $tema['contexto_defecto'] ? 'checked' : '' ?>>
                <label class="control-label" for="contexto_defecto">Dejar valores por defecto para este campo</label>
            </div>
            <div class="tab-pane fade" id="seccion_contenidos" role="tabpanel" aria-labelledby="tab_contenidos">
                <label class="control-label" for="contenidos">Contenidos</label>
                <textarea name="contenidos" class="datostema" id="contenidos"><?= $tema['contenidos'] ?></textarea>
            </div>
            <div class="tab-pane fade" id="seccion_secuenciacion" role="tabpanel" aria-labelledby="tab_secuenciacion">
                <label class="control-label" for="secuenciacion">Secuenciación</label>
                <textarea name="secuenciacion" class="datostema" id="secuenciacion"><?= $tema['secuenciacion'] ?></textarea>
            </div>
            <div class="tab-pane fade" id="seccion_recursos" role="tabpanel" aria-labelledby="tab_recursos">
                <label class="control-label" for="recursos">Recursos</label>
                <textarea name="recursos" class="datostema" id="recursos"><?= $tema['recursos'] ?></textarea>
                <input type="checkbox" id="recursos_defecto" name="recursos_defecto" <?= $tema['recursos_defecto'] ? 'checked' : '' ?>>
                <label class="control-label" for="recursos_defecto">Dejar valores por defecto para este campo</label>
            </div>
            <div class="tab-pane fade" id="seccion_evaluacion" role="tabpanel" aria-labelledby="tab_evaluacion">
                <label class="control-label" for="evaluacion">Evaluación</label>
                <textarea name="evaluacion" class="datostema" id="evaluacion"><?= $tema['evaluacion'] ?></textarea>
                <button class="btn btn-primary" type="button" onclick="repetirEvaluacion()">Repetir en resto de unidades</button>
            </div>
            <div class="tab-pane fade" id="seccion_metodologia" role="tabpanel" aria-labelledby="tab_metodologia">
                <label class="control-label" for="metodologia">Metodología</label>
                <textarea name="metodologia" class="datostema" id="metodologia"><?= $tema['metodologia'] ?></textarea>
                <input type="checkbox" id="metodologia_defecto" name="metodologia_defecto" <?= $tema['metodologia_defecto'] ? 'checked' : '' ?>>
                <label class="control-label" for="metodologia_defecto">Dejar valores por defecto para este campo</label>
            </div>
            <div class="tab-pane fade" id="seccion_adaptaciones" role="tabpanel" aria-labelledby="tab_adaptaciones">
                <label class="control-label" for="adaptaciones">Adaptaciones</label>
                <textarea name="adaptaciones" class="datostema" id="adaptaciones"><?= $tema['adaptaciones'] ?></textarea>
                <input type="checkbox" id="adaptaciones_defecto" name="adaptaciones_defecto" <?= $tema['adaptaciones_defecto'] ? 'checked' : '' ?>>
                <label class="control-label" for="adaptaciones_defecto">Dejar valores por defecto para este campo</label>
            </div>
            <!-- Añadimos pestaña para insertar un accordion con los RA y CE con javascript -->
            <div class="tab-pane fade" id="seccion_ra_ce" role="tabpanel" aria-labelledby="tab_ra_ce">;
            </div>
            <?php
                // Añadimos pestaña para competencias profesionales, para la empleabilidad, etc.
                echo '<div class="tab-pane fade" id="seccion_competencias" role="tabpanel" aria-labelledby="tab_competencias">';
                echo generarCheckboxesCompetenciasMateria($idMateria, $idCiclo);
                echo '</div>';
            ?>
        </div>                    
        <!-- Botones finales -->
        <div class="form-group text-center mb-2">
            <button class="btn btn-primary" type="submit">Enviar</button>
            <a href="temas.php?idMateria=<?= $idMateria; ?>" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

<script src="js/temas.js?v=9"></script>	

<script>
    selMateria = <?= $idMateria; ?>;
    idCiclo = <?= $idCiclo; ?>;
    idTema = <?= $idTema; ?>;

    document.addEventListener('DOMContentLoaded', function() {
        initTinyMCE('datostema', 350);
        cargarAccordionRAyCE();
    });
</script>

<?php
    require_once('modales/edicion_ra_unidades.php');
    require_once('includes/database2.php');
    require_once('includes/pie.php');
?>