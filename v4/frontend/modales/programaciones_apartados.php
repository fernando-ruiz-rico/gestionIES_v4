<!-- Ventana modal para rellenar los datos de los cursos -->
<!-- El id "formapartadoprogramacion" se usa para mostrar el modal -->
<div id="formapartadoprogramacion" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de apartado de programación didáctica</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formapartado" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el apartado seleccionado -->
                <form id="formapartado" name="formapartado" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="idApartado" value="">
                    <div class="form-group mb-2">
                        <label class="control-label" for="titulo">Título del apartado</label>
                        <input class="form-control" type="text" name="titulo" id="titulo" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="control-label" for="categoria">Categoría del curso</label>
                        <select class="form-control" name="categoria" id="categoria" required>
                            <option value="">--Selecciona una categoría--</option>
                            <option value="ESO/BACH">ESO / Bachillerato</option>
                            <option value="FP">FP</option>
                            <option value="TODOS">Todos</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="control-label" for="tipo">Tipo de apartado</label>
                        <input class="form-control" type="number" name="tipo" id="tipo" value="0" required>
                    </div>
                    <div class="form-group mb-2">
                        <input type="checkbox" name="subapartado" id="subapartado"> Es un subapartado
                        <br />
                        <input type="checkbox" name="requerido" id="requerido"> Es obligatorio rellenarlo
                        <br />
                        <input type="checkbox" name="contenidoDefecto" id="contenidoDefecto"> Admite contenido por defecto
                    </div>
                    <div class="form-group">
                        <button class="btn btn-light" type="submit">Enviar</button>
                        <button class="btn btn-danger type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>   