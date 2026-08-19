<!-- Ventana modal para rellenar los datos de los grupos de cursos -->
<!-- El id "formgrupo" se usa para mostrar el modal -->
<div id="formgrupo" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de grupo</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formgrup" se usa para enviar el formulario por jQuery -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el grupo seleccionado -->
                <form id="formgrup" name="formgrup" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="idGrupo" value="">
                    <input type="hidden" name="idCurso" id="idCurso" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre del grupo (por ejemplo, A, B semipresencial...)</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="abreviatura">Nombre abreviado</label>
                        <input class="form-control" type="text" name="abreviatura" id="abreviatura" size="10" required>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="mostrar" id="mostrar"> Mostrar información del grupo en los listados
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasComplementariasDual">Horas complementarias semanales para cada profesor por ser grupo con FP Dual</label>
                        <input class="form-control" type="text" name="horasComplementariasDual" id="horasComplementariasDual" size="10" required>
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