<!-- Ventana modal para rellenar los datos de las especialidades -->
<!-- El id "formespecialidad" se usa para mostrar el modal -->
<div id="formespecialidad" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de especialidades</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formesp" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX para la especialidad seleccionada -->
                <form id="formesp" name="formesp" method="post" enctype="multipart/form-data">
                    <!-- Id antiguo de la especialidad (se rellena en caso de edición) -->
                    <input type="hidden" name="idAntiguo" id="idAntiguo" value="">
                    <!-- Id del departamento al que corresponde la especialidad -->
                    <input type="hidden" name="idDepartamento" id="idDepartamento" value="">
                    <div class="form-group">
                        <label class="control-label" for="idEspecialidad">Id. de especialidad (3 letras)</label>
                        <input class="form-control" type="text" name="id" id="idEspecialidad" maxlength="3" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="descripcion">Descripción</label>
                        <input class="form-control" type="text" name="descripcion" id="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasTutoria">Horas asignadas de tutoría (estimación)</label>
                        <input class="form-control" type="text" name="horasTutoria" id="horasTutoria">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasIngles">Horas asignadas de inglés (estimación)</label>
                        <input class="form-control" type="text" name="horasIngles" id="horasIngles">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="profesores">Número de profesores</label>
                        <input class="form-control" type="text" name="profesores" id="profesores">
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