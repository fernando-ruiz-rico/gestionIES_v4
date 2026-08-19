<!-- Ventana modal para rellenar los datos de los cursos -->
<!-- El id "formcurso" se usa para mostrar el modal -->
<div id="formcurso" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de curso</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formcur" se usa para enviar el formulario por JavaScript -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el curso seleccionado -->
                <form id="formcur" name="formcur" method="post" enctype="multipart/form-data">
                    <!-- Id del curso (se rellena en caso de edición) -->
                    <input type="hidden" name="id" id="idCurso" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre del curso</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="categoria">Categoría del curso</label>
                        <select class="form-control" name="categoria" id="categoria" required>
                            <option value="">--Selecciona una categoría--</option>
                            <option value="ESO">ESO</option>
                            <option value="BACH">Bachillerato</option>
                            <option value="FP">FP</option>
                            <option value="OTROS">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="abreviatura">Nombre abreviado (por ejemplo, 2DAM, 1SMR, 3ESO...)</label>
                        <input class="form-control" type="text" name="abreviatura" id="abreviatura" size="10" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasSemana">Horas de clase semanales</label>
                        <input class="form-control" type="text" name="horasSemana" id="horasSemana" placeholder="Deja el campo vacío o pon 0 si el curso no debe sumar X horas semanales">
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