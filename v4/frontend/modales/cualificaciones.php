<!-- Ventana modal para rellenar los datos de las cualificaciones profesionales -->
<!-- El id "formcualificacion" se usa para mostrar el modal -->
<div id="formcualificacion" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de cualificaciones</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formcua" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX -->
                <form id="formcua" name="formcua" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idCualificacion" id="idCualificacion" value="">
                    <div class="form-group">
                        <label class="control-label" for="codigoCualificacion">Código</label>
                        <input class="form-control" type="text" name="codigoCualificacion" id="codigoCualificacion" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="textoCualificacion">Texto</label>
                        <input class="form-control" type="text" name="textoCualificacion" id="textoCualificacion" required>
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