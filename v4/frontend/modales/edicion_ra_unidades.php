<!-- Ventana modal para editar los datos de resultados de aprendizaje en la edición de unidades -->

<?php $prefijo = $idCiclo > 0 ? 'RA' : 'CE'; ?>

<!-- El id "formresultado_ra" se usa para mostrar el modal -->
<div id="formresultado_ra" class="modal fade" role="dialog" data-bs-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?= $idCiclo > 0 ? 'Evaluación del Resultado de Aprendizaje (RA)' : 'Evaluación de la Competencia Específica (CE)'; ?></h3>
            </div>
            <div class="modal-body">
                <!-- El id "formres_ra" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el resultado seleccionado -->
                <form id="formres_ra" name="formres_ra" method="post" enctype="multipart/form-data">
                    <!-- Id del resultado (se rellena en caso de edición) -->
                    <input type="hidden" name="idResultado" id="idResultado" value="">

                    <h6 class="mb-4"><?= $prefijo ?><span id="spanOrden"></span>. <span id="spanTexto"></span></h6>

                    <div class="form-group">
                        <label class="control-label" for="porcentajeEvaluacion">Porcentaje en la evaluación global</label>

                        <div class="input-group mb-3">
                            <input type="number" class="form-control" name="porcentajeEvaluacion" id="porcentajeEvaluacion"  aria-label="caracter porcentaje" aria-describedby="caracterPorcentaje" min="0" max="100" required>
                            <span class="input-group-text" id="caracterPorcentaje">%</span>
                        </div>
                    </div>

                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="esClave" id="esClave">
                        <label class="form-check-label" for="esClave"><?= $prefijo ?> clave</label>
                        <div id="descripcionEsClave" class="form-text"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Enviar</button>
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Actualizar texto del label de RA clave según estado del checkbox
    $('#esClave').on('change', function() {
        const label = $('#descripcionEsClave');
        if (this.checked) {
            label.text('(Se debe superar para aprobar la materia)');
        } else {
            label.text('(Se puede no superar y aprobar la materia)');
        }
    });

    // Evento de envío del formulario modal para inserción/modificación
    $("#formres_ra").on("submit", function(e)
    {
        e.preventDefault();
        let formData = new FormData(document.forms.formres_ra);

        $.ajax({
            url: "ajax/resultados_aprendizaje/actualizar_resultado_aprendizaje_evaluacion.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            $("#formresultado_ra").modal('hide');
            cargarAccordionRAyCE();
        });
    });

    // Asegurar estado inicial al cargar (por si acaso)
    $(document).ready(function() {
        $('#esClave').trigger('change');
    });
</script>