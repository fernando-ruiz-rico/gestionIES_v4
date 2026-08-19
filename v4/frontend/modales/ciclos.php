<!-- Ventana modal para rellenar los datos de los ciclos formativos -->
<!-- El id "formciclo" se usa para mostrar el modal -->
<div id="formciclo" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de ciclo formativo</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formcic" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el ciclo seleccionado -->
                <form id="formcic" name="formcic" method="post" enctype="multipart/form-data">
                    <!-- Id del ciclo (se rellena en caso de edición) -->
                    <input type="hidden" name="id" id="idCiclo" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre del ciclo</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="familia">Familia</label>
                        <input class="form-control" type="text" name="familia" id="familia" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="nivel">Nivel</label>
                        <select class="form-control" name="nivel" id="nivel" required>
                            <option value="">--Selecciona un nivel--</option>
                            <option value="Ciclo Formativo de Grado Básico">Ciclo Formativo de Grado Básico</option>
                            <option value="Ciclo Formativo de Grado Medio">Ciclo Formativo de Grado Medio</option>
                            <option value="Ciclo Formativo de Grado Superior">Ciclo Formativo de Grado Superior</option>
                            <option value="Curso de Especialización">Curso de Especialización</option>
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