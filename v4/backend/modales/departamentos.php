<!-- Formulario modal para el alta/edición de departamentos -->
<!-- El id "formdepartamento" se usa para mostrar el modal -->
<div id="formdepartamento" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de departamentos</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formdep" se usa para enviar el formulario por JavaScript -->
                <form id="formdep" name="formdep" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="idDepartamento" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre del departamento</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
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