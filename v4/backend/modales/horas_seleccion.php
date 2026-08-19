<!-- Formulario modal para elegir cuántas horas se eligen de una materia en las desideratas -->
<div id="formhorasseleccion" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Horas para la materia seleccionada</h3>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idMateria" id="idMateria" value="">
                <input type="hidden" name="idGrupo" id="idGrupo" value="">
                <div class="form-group">
                    <label class="control-label" for="horas">Horas para la materia seleccionada (por defecto, todas):</label>
                    <input class="form-control" type="number" name="horas" id="horas" required>
                </div>
                <div class="form-group">
                    <button class="btn btn-light" onclick="seleccionarHoras()">Enviar</button>
                    <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>   