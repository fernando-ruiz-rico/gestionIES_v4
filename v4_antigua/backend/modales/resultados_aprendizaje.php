<!-- Ventana modal para rellenar los datos de resultados de aprendizaje -->

<?php
// Variable para determinar si ciertos campos son editables o de solo lectura
// La variable $permisos se crea en la cabecera
$readonly = $permisos?'':'readonly';
?>

<!-- El id "formresultado" se usa para mostrar el modal -->
<div id="formresultado" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de resultados de aprendizaje</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formres" se usa para enviar el formulario por JavaScript -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el resultado seleccionado -->
                <form id="formres" name="formres" method="post" enctype="multipart/form-data">
                    <!-- Id del resultado (se rellena en caso de edición) -->
                    <input type="hidden" name="id" id="idResultado" value="">
                    <!-- Id de la materia asociada -->
                    <input type="hidden" name="idMateria" id="idMateria" value="">
                    <div class="form-group">
                        <label class="control-label" for="orden">Orden</label>
                        <input class="form-control" type="number" name="orden" id="orden" <?=$readonly?> required>
                    </div>
                    <div class="form-group mt-2">
                        <label class="control-label" for="texto">Texto</label>
                        <input class="form-control" type="text" name="texto" id="texto" <?=$readonly?> required>
                    </div>
                    <div class="form-group mt-2">
                        <label class="control-label" for="porcentajeEmpresa">Porcentaje de docencia asignado a la empresa</label>
                        <select class="form-control" name="porcentajeEmpresa" id="porcentajeEmpresa" required>
                            <option value="0" selected>0%</option>
                            <?php
                                for($i = 5; $i <= 100; $i += 5)
                                {
                                    echo '<option value="' . $i . '">' . $i . ' %</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <button class="btn btn-light" type="submit">Enviar</button>
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
