<!-- Ventana modal para rellenar los datos de las competencias de ciclos -->
<!-- El id "formcompetencia" se usa para mostrar el modal -->
<div id="formcompetencia" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de competencias</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formcomp" se usa para enviar el formulario por JavaScript -->
                <!-- Todos los datos del formulario se rellenan por AJAX para la competencia seleccionada -->
                <form id="formcomp" name="formcomp" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="idCompetencia" value="">
                    <input type="hidden" name="idCiclo" id="idCiclo" value="">
                    <div class="form-group">
                        <label class="control-label" for="codigo">Código de competencia</label>
                        <input class="form-control" type="text" name="codigo" id="codigo" maxlength="3" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="texto">Texto</label>
                        <input class="form-control" type="text" name="texto" id="texto" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="tipo">Tipo</label>
                        <select class="form-control" name="tipo" id="tipo" required>
                            <option value="">--Selecciona un tipo--</option>
                            <option value="1">Profesional</option>
                            <option value="2">Para la empleabilidad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-light" type="submit">Enviar</button>
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>   