<!-- Ventana modal para importar datos de otra programación didáctica -->
<!-- El id "formimportarprog" se usa para mostrar el modal -->
<div id="formimportarprog" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Importar datos de otra programación</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formimpprog" se usa para enviar el formulario por JavaScript -->
                <form id="formimpprog" name="forimpprog" method="post" enctype="multipart/form-data">
                    <!-- Id de la materia destino -->
                    <input type="hidden" name="idMateriaDestino" id="idMateriaDestino" value="">
                    <div class="form-group">
                        <label class="control-label" for="idMateriaOrigen">Selecciona materia desde la que importar</label>
                        <select class="form-control" name="idMateriaOrigen" id="idMateriaOrigen" required>
                            <option value="">--Selecciona una materia--</option>
                            <?php
                            include('includes/cargar_materias_programaciones.php');
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-light" type="submit">Importar</button>
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>   
