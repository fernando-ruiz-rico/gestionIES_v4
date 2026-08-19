<!-- Ventana modal para rellenar los datos de un nuevo tema/unidad de una programación -->
<!-- El id "formnuevotema" se usa para mostrar el modal -->
<div id="formnuevotema" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de nuevo tema/unidad</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formnuevo" se usa para enviar el formulario por JavaScript -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el ciclo seleccionado -->
                <form id="formnuevo" name="formnuevo" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idMateria" id="idMateria" value="<?= $_REQUEST['idMateria'] ?>">
                    <div class="form-group">
                        <label class="control-label" for="ordenNuevo">Número de tema</label>
                        <input class="form-control" type="number" name="orden" id="ordenNuevo" min="1" required>
                    </div>
                    <div class="form-group mt-2">
                        <label class="control-label" for="tituloNuevo">Título</label>
                        <input class="form-control" type="text" name="titulo" id="tituloNuevo" required>
                    </div>
                    <div class="form-group mt-2">
                        <button class="btn btn-primary" type="submit">Enviar</button>
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>   