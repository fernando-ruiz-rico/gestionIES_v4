<!-- Ventana modal para rellenar los datos de las materias de cursos -->
<!-- El id "formmateria" se usa para mostrar el modal -->
<div id="formmateria" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de alta/edición de materia</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formmat" se usa para enviar el formulario por JavaScript -->
                <!-- Todos los datos del formulario se rellenan por AJAX para la materia seleccionada -->
                <form id="formmat" name="formmat" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idCurso" id="idCurso" value="">
                    <input type="hidden" name="id" id="idMateria" value="">
                    <div class="form-group">
                        <label class="control-label" for="nombre">Nombre</label>
                        <input class="form-control" type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="codigoOficial">Código oficial</label>
                        <input class="form-control" type="text" name="codigoOficial" id="codigoOficial">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="nombreOficial">Nombre oficial</label>
                        <input class="form-control" type="text" name="nombreOficial" id="nombreOficial">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="creditosECTS">Créditos ECTS</label>
                        <input class="form-control" type="number" name="creditosECTS" id="creditosECTS">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasAnuales">Horas anuales</label>
                        <input class="form-control" type="number" name="horasAnuales" id="horasAnuales">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="tipo">Tipo de materia</label>                           
                        <select class="form-control" name="tipo" id="tipo">
                            <option value="TUTORIA">Tutoría</option>
                            <option value="INGLES">Inglés</option>
                            <option value="OTRA" selected="selected">Otras materias</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="departamento">Departamento</label>                           
                        <select class="form-control" name="departamento" id="departamento" onchange="cargarEspecialidades()">
                            <option value="" selected="selected">--Selecciona un departamento--</option>
                            <?php
                            include('includes/database.php');
                            $resultado = mysqli_query($db, "SELECT * FROM departamentos");
                            while ($fila = mysqli_fetch_assoc($resultado))
                            {
                                echo '<option value="' . $fila['id'] . '">' . $fila['nombre'] . '</option>';
                            }
                            mysqli_free_result($resultado);
                            include('includes/database2.php');
                            ?>
                        </select>
                    </div>                    
                    <div class="form-group">
                        <label class="control-label" for="especialidad">Especialidad</label>                           
                        <select class="form-control" name="especialidad" id="especialidad">
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="computablesHorasGrupo" id="computablesHorasGrupo" checked="checked"> Computables para las horas semanales del grupo
                        <br>
                        <input type="checkbox" name="asignadaDirectiva" id="asignadaDirectiva"> Asignada por el equipo directivo
                        <br>
                        <input type="checkbox" name="tieneProgramacion" id="tieneProgramacion" checked="checked"> Tiene programación didáctica asociada
                        <br>
                        <input type="checkbox" name="divisible" id="divisible" checked="checked"> Divisible                    
                    </div>
                    <div class="form-group">
                        <p><strong>Información de referencia para la materia (a concretar en cada grupo)</strong></p>
                    </div>                    
                    <div class="form-group">
                        <label class="control-label" for="cantidad">Cantidad de unidades por grupo</label>
                        <input class="form-control" type="number" name="cantidad" id="cantidad" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horas">Horas / semana</label>
                        <input class="form-control" type="number" name="horas" id="horas" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="horasComplementarias">Horas complementarias / semana</label>
                        <input class="form-control" type="number" name="horasComplementarias" id="horasComplementarias" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="minNumProfesores">Mínimo número de profesores (0 para no limitar)</label>
                        <input class="form-control" type="number" name="minNumProfesores" id="minNumProfesores" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="maxGruposProfesor">Máximo número de grupos por profesor (0 para no limitar)</label>
                        <input class="form-control" type="number" name="maxGruposProfesor" id="maxGruposProfesor" value="0" required>
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
