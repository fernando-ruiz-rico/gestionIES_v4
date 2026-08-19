<!-- Ventana modal para rellenar los datos de las unidades de competencia -->
<!-- El id "formunidad" se usa para mostrar el modal -->
<div id="formunidad" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de unidades de competencia</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formuni" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX -->
                <form id="formuni" name="formuni" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idUnidad" id="idUnidad" value="">
                    <div class="form-group">
                        <label class="control-label" for="codigoUnidad">Código</label>
                        <input class="form-control" type="text" name="codigoUnidad" id="codigoUnidad" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="textoUnidad">Texto</label>
                        <input class="form-control" type="text" name="textoUnidad" id="textoUnidad" required>
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