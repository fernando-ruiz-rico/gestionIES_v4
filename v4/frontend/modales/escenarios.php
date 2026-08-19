<!-- Ventana modal para rellenar los datos de escenarios de desideratas -->
<!-- El id "formescenario" se usa para mostrar el modal -->
<div id="formescenario" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de escenario para desideratas</h3>
            </div>
            <div class="modal-body">
                <form id="formesc" name="formesc" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="idEscenario" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre del escenario</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <p>Departamentos asignados al escenario</p>
                        <div id="listadoDepartamentosEscenario" style="max-height:200px;overflow-y:auto">
                            <!-- Aquí se cargarán por AJAX los departamentos para asignar al escenario -->
                        </div>
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