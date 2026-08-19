<!-- Ventana modal para editar perfil de un profesor. Se invoca o bien para cada profesor
     de la lista de profesores en la vista "profesores.php" (por parte del administrador), 
     o bien desde el menú "Perfil" para editar los datos del profesor que ha accedido a la 
     aplicación -->
<!-- El id "formprofesor" se usa para abrir/cerrar el modal desde JavaScript -->
<div id="formprofesor" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Formulario de perfil de profesor</h3>
            </div>
            <div class="modal-body">
                <!-- El id "formprof" se usa desde JavaScript para enviar el formulario por AJAX -->
                <!-- Todos los datos del formulario se rellenan por AJAX para el profesor seleccionado -->
                <form id="formprof" name="formprof" method="post" enctype="multipart/form-data">
                    <!-- Id del profesor y del departamento -->
                    <input type="hidden" name="id" id="idPerfil" value="">
                    <input type="hidden" name="idDepartamento" id="idDepartamentoPerfil" value="">
                    <!-- Aquí guardamos con una codificación especial las preferencias de horario
                         rojas y amarillas del profesor (rojas = no quiere estar, amarillas = 
                         prefiere no estar) -->
                    <input type="hidden" name="prefRojas" id="prefRojas" value="">
                    <input type="hidden" name="prefAmarillas" id="prefAmarillas" value="">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="control-label" for="nombrePerfil">Nombre</label>
                                <input class="form-control" type="text" name="nombre" id="nombrePerfil" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="abreviaturaPerfil">Abreviatura del nombre</label>
                                <input class="form-control" type="text" name="abreviatura" id="abreviaturaPerfil" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="usuarioPerfil">Login de usuario</label>
                                <input class="form-control" type="text" name="usuario" id="usuarioPerfil" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="clavePerfil">Clave</label>
                                <input class="form-control" type="password" name="clave" id="clavePerfil">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="emailPerfil">E-mail</label>
                                <input class="form-control" type="email" name="email" id="emailPerfil">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="telefonoPerfil">Teléfono</label>
                                <input class="form-control" type="text" name="telefono" id="telefonoPerfil">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="idEspecialidadPerfil">Especialidad</label>       
                                <!-- Las especialidades a elegir se cargan por AJAX cuando se invoca este formulario -->                    
                                <select class="form-control" name="idEspecialidad" id="idEspecialidadPerfil" required></select>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="observacionesPerfil">Observaciones referentes al horario</label>
                                <textarea class="form-control" rows="5" name="observaciones" id="observacionesPerfil"></textarea>
                            </div>  
                            <div class="form-group">
                                <button class="btn btn-light" type="submit">Enviar</button>
                                <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                        <div class="col-6">
                            <p>Preferencias de horario&nbsp;&nbsp;<span class="badge bg-warning" title="Deja en rojo las casillas donde no quieras tener clase, y en amarillo donde preferirías no tener clase. Cambia el color de las casillas haciendo clic sobre ellas.">?</span></p>
                            <!-- Este div se rellena por AJAX con un horario semanal donde se pueden marcar las preferencias horarias -->
                            <div id="prefhoras">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  
