<?php
// -------------------------------
// Genera los checkboxes de los criterios de evaluación para un RA dado
// -------------------------------
function generarCheckboxesCE($idRA, $ordenRA) {
    $sqlCE = "SELECT codigo, texto FROM criterios_evaluacion WHERE idRA = {$idRA} ORDER BY codigo";
    $criterios = consultarBaseDeDatos($sqlCE);

    $html = '';
    if (!empty($criterios)) {
        foreach ($criterios as $ce) {
            $codigoCE = $ce['codigo'];
            $textoCE = $ce['texto'];
            $valor = "ce_{$idRA}_{$codigoCE}";
            $html .= "
                <div class='form-check mb-0'>
                    <input class='form-check-input me-2 check_ce ra{$idRA}' type='checkbox' name='ce[]' id='{$valor}' value='{$valor}'>
                    <label class='form-check-label d-inline-block text-truncate w-100' title='{$textoCE}' for='{$valor}'>
                        {$ordenRA}.{$codigoCE}. {$textoCE}
                    </label>
                </div>";
        }
    } else {
        $html .= "<em class='text-muted'>No hay criterios de evaluación definidos.</em>";
    }

    return $html;
}

// -------------------------------
// Genera los checkboxes de resultados de aprendizaje y criterios de evaluación para una materia dada
// -------------------------------
function generarAccordionResultadosAprendizaje($idMateria, $idCiclo)
{
    $html = '<p class="mb-2 d-flex justify-content-between align-items-center">';
    if ($idCiclo > 0) {
        $html .= 'Resultados de aprendizaje y criterios de evaluación';
    } else {
        $html .= 'Competencias específicas y criterios de evaluación';
    }
    $html .= '<button type="button" class="btn btn-sm btn-secondary" onclick="calcularPorcentajesRAyCE()">Calcular y actualizar porcentajes</button>'; // Esto irá a la derecha
    $html .= '</p>';

    $sqlRA = "SELECT id, orden, texto, porcentaje_evaluacion FROM resultados_aprendizaje WHERE idMateria = {$idMateria} ORDER BY orden";
    $resultadosAprendizaje = consultarBaseDeDatos($sqlRA);

    if (empty($resultadosAprendizaje)) {
        return "<p class='text-muted'>No hay resultados de aprendizaje definidos para esta materia.</p>";
    }

    $html .= "<div class='accordion' id='accordionRA'>";

    $total = 0;
    foreach ($resultadosAprendizaje as $ra) {
        $id = (int)$ra['id'];
        $orden = (int)$ra['orden'];
        $texto = $ra['texto'];
        $porcentajeEvaluacion = (int)$ra['porcentaje_evaluacion'];
        $total += $porcentajeEvaluacion;
        $tituloCompleto = "{$orden}. {$texto}";

        $html .= "
            <div class='accordion-item mb-0'>
                <h2 class='accordion-header' id='headingRA{$orden}'>
                    <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapseRA{$orden}' aria-expanded='false' aria-controls='collapseRA{$orden}'>
                        <input type='checkbox' class='form-check-input mt-0 me-2 check_ra' id='ra{$id}' onclick='marcarDesmarcar({$id})'>
                        <span class='d-inline-block text-truncate' style='width: 95%' title='{$texto}'>{$tituloCompleto}</span>
                        <span title='Pulsa para cambiar' class='btn btn-sm btn-secondary px-2 py-0 mx-2' onclick='cargarModalActualizarRaTemas({$id});' style='min-width: 5%'>{$porcentajeEvaluacion}&nbsp;%</span>
                    </button>
                </h2>
                <div id='collapseRA{$orden}' class='accordion-collapse collapse' data-bs-parent='#accordionRA'>
                    <div class='accordion-body'>";

        $html .= generarCheckboxesCE($id, $orden);
        $html .= "
                    </div>
                </div>
            </div>";
    }

    $color = $total != 100 ? 'text-danger' : 'text-success';
    $html .= "<div class='accordion-item text-center p-1 mb-0'><span class='{$color}'>Suma: {$total}%</span> (evaluación anual)</div>";

    $html .= "</div>";
    return $html;
}

if (!empty($_REQUEST['idMateria']) && isset($_REQUEST['idCiclo']))
{
    require_once('../../includes/database.php');
    require_once('../../includes/utilidades.php');
    echo generarAccordionResultadosAprendizaje($_REQUEST['idMateria'], $_REQUEST['idCiclo']);
    require_once('../../includes/database2.php');
}
?>