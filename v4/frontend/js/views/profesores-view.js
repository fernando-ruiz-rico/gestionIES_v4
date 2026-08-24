// Componente Profesores View (gestión de profesores por departamento)
const ProfesoresView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-3">
                        <i class="bi bi-people me-2"></i>Profesores por departamento
                    </h1>
                    <p class="text-muted">
                        <em>Arrastra los profesores para ordenarlos entre sí. Haz clic en el icono del lápiz para editar los datos de cada profesor, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar profesores con el icono de borrar junto a cada apartado. También puedes elegir al jefe de departamento haciendo clic en el icono de la medalla (aparece en verde el icono para el actual jefe de departamento)</em>
                    </p>
                </div>
            </div>
            
            <!-- Selector de departamento -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 h6"><i class="bi bi-building me-2"></i>Seleccionar departamento</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="selectorDepartamento">Departamento</label>
                            <select class="form-select" id="selectorDepartamento" v-model="idDepartamentoSeleccionado">
                                <option value="">-- Seleccionar departamento --</option>
                                <option v-for="dept in departamentos" :key="dept.id" :value="dept.id">
                                    {{ dept.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenedor de mensajes -->
            <div id="mensajes" class="mb-3"></div>
            
            <!-- Listado de profesores -->
            <div class="card shadow-sm mb-4" v-if="idDepartamentoSeleccionado">
                <div class="card-header">
                    <h5 class="mb-0 h6"><i class="bi bi-list-ul me-2"></i>Listado de profesores</h5>
                </div>
                <div class="card-body p-0">
                    <div id="listaprofesores" class="list-group list-group-flush">
                        <div v-if="cargando" class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <div v-else-if="profesores.length === 0" class="text-center p-4 text-muted">
                            No hay profesores en este departamento
                        </div>
                        <div v-else 
                             class="list-group-item d-flex justify-content-between align-items-center"
                             v-for="profesor in profesores"
                             :key="'pr' + profesor.id"
                             :id="'pr' + profesor.id">
                            <div class="flex-grow-1">
                                <strong>{{ profesor.nombre }}</strong>
                                <span v-if="profesor.jefe_departamento == 1" class="badge bg-success ms-2">
                                    <i class="bi bi-star-fill"></i> Jefe
                                </span>
                                <span v-if="profesor.activo == 0" class="badge bg-secondary ms-2">Inactivo</span>
                            </div>
                            <div class="btn-group" role="group">
                                <!-- Botón activar/desactivar -->
                                <button class="btn btn-sm btn-light" 
                                        :title="profesor.activo == 1 ? 'Desactivar profesor' : 'Activar profesor'"
                                        @click="cambiarActivo(profesor.id)">
                                    <i :class="profesor.activo == 1 ? 'bi bi-toggle-on text-success' : 'bi bi-toggle-off text-danger'"></i>
                                </button>
                                <!-- Botón jefe de departamento -->
                                <button class="btn btn-sm" 
                                        :class="profesor.jefe_departamento == 1 ? 'btn-success' : 'btn-light'"
                                        title="Elegir jefe de departamento"
                                        @click="cambiarJefe(profesor.id)">
                                    <i class="bi bi-award-fill"></i>
                                </button>
                                <!-- Botón editar -->
                                <button class="btn btn-sm btn-light" 
                                        title="Editar profesor"
                                        @click="editarProfesor(profesor.id)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- Botón borrar -->
                                <button class="btn btn-sm btn-light" 
                                        title="Borrar profesor"
                                        @click="borrarProfesor(profesor.id, profesor.nombre)">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón para abrir el diálogo modal para crear nuevos profesores -->
            <div class="text-center" v-if="idDepartamentoSeleccionado">
                <button class="btn btn-primary" @click="nuevoProfesor">
                    <i class="bi bi-plus-lg me-2"></i>Nuevo Profesor
                </button>
            </div>
            
            <!-- Diálogo modal para crear/editar profesores -->
            <div id="formprofesor" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-person me-2"></i>Formulario de perfil de profesor
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formprof" name="formprof" method="post" enctype="multipart/form-data" @submit.prevent="guardarProfesor">
                                <input type="hidden" name="id" id="idPerfil" v-model="formulario.id">
                                <input type="hidden" name="idDepartamento" id="idDepartamentoPerfil" :value="idDepartamentoSeleccionado">
                                <input type="hidden" name="prefRojas" id="prefRojas" v-model="formulario.prefRojas">
                                <input type="hidden" name="prefAmarillas" id="prefAmarillas" v-model="formulario.prefAmarillas">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="nombrePerfil">Nombre</label>
                                            <input class="form-control" type="text" name="nombre" id="nombrePerfil" v-model="formulario.nombre" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="abreviaturaPerfil">Abreviatura del nombre</label>
                                            <input class="form-control" type="text" name="abreviatura" id="abreviaturaPerfil" v-model="formulario.abreviatura" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="usuarioPerfil">Login de usuario</label>
                                            <input class="form-control" type="text" name="usuario" id="usuarioPerfil" v-model="formulario.usuario" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="clavePerfil">Clave</label>
                                            <input class="form-control" type="password" name="clave" id="clavePerfil" v-model="formulario.clave" placeholder="Dejar vacío si no se cambia">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="emailPerfil">E-mail</label>
                                            <input class="form-control" type="email" name="email" id="emailPerfil" v-model="formulario.email">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="telefonoPerfil">Teléfono</label>
                                            <input class="form-control" type="text" name="telefono" id="telefonoPerfil" v-model="formulario.telefono">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="idEspecialidadPerfil">Especialidad</label>
                                            <select class="form-select" name="idEspecialidad" id="idEspecialidadPerfil" v-model="formulario.idEspecialidad" required>
                                                <option value="">-- Seleccionar especialidad --</option>
                                                <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">{{ esp.nombre }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="observacionesPerfil">Observaciones referentes al horario</label>
                                            <textarea class="form-control" rows="5" name="observaciones" id="observacionesPerfil" v-model="formulario.observaciones"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="fw-bold">Preferencias de horario&nbsp;
                                            <span class="badge bg-warning text-dark" title="Deja en rojo las casillas donde no quieras tener clase, y en amarillo donde preferirías no tener clase. Cambia el color de las casillas haciendo clic sobre ellas.">?</span>
                                        </p>
                                        <div id="prefhoras" class="preferencias-container">
                                            <!-- Tabla de preferencias horarias -->
                                            <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th v-for="dia in dias" :key="dia">{{ dia }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="hora in horasManana" :key="'M-'+hora">
                                                        <th>{{ hora }}</th>
                                                        <td v-for="dia in dias" 
                                                            :key="dia+'-'+hora"
                                                            :class="obtenerClaseCelda(dia, hora)"
                                                            @click="togglePreferencia(dia, hora)">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="6" class="bg-light">&nbsp;</th>
                                                    </tr>
                                                    <tr v-for="hora in horasTarde" :key="'T-'+hora">
                                                        <th>{{ hora }}</th>
                                                        <td v-for="dia in dias" 
                                                            :key="dia+'-'+hora"
                                                            :class="obtenerClaseCelda(dia, hora)"
                                                            @click="togglePreferencia(dia, hora)">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </div>
                                            <small class="text-muted">
                                                Máximo <strong>{{ maxRojas }}</strong> casillas rojas (actualmente {{ contRojas }})
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                    <button class="btn btn-secondary me-md-2" type="button" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg me-1"></i>Cancelar
                                    </button>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-check-lg me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,
    
    data() {
        return {
            departamentos: [],
            especialidades: [],
            idDepartamentoSeleccionado: null,
            profesores: [],
            cargando: false,
            formulario: {
                id: null,
                nombre: '',
                abreviatura: '',
                usuario: '',
                clave: '',
                email: '',
                telefono: '',
                idEspecialidad: '',
                observaciones: '',
                prefRojas: '',
                prefAmarillas: ''
            },
            dias: ['L', 'M', 'X', 'J', 'V'],
            horasManana: ['8:00', '9:00', '10:00', '11:00', '12:00', '13:00'],
            horasTarde: ['15:00', '16:00', '17:00', '18:00', '19:00', '20:00'],
            maxRojas: 3,
            contRojas: 0,
            modalInstance: null
        };
    },
    
    async mounted() {
        await this.cargarDepartamentos();
        await this.cargarEspecialidades();
        
        // Inicializar modal Bootstrap
        this.modalInstance = new bootstrap.Modal(document.getElementById('formprofesor'));
    },
    
    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', {
                credentials: 'include'
            });
            if (result.ok) {
                const data = await result.json();
                if (data.success) this.departamentos = data.data || [];
            }
        },
        
        async cargarEspecialidades() {
            const result = await fetch('../backend/api/especialidades/listar.php', {
                credentials: 'include'
            });
            if (result.ok) {
                const data = await result.json();
                this.especialidades = (data.success && Array.isArray(data.data)) ? data.data : [];
            }
        },
        
        async cargarProfesores() {
            if (!this.idDepartamentoSeleccionado) return;
            
            this.cargando = true;
            const result = await ProfesoresAPI.listar(this.idDepartamentoSeleccionado);
            this.cargando = false;
            
            if (result.success) {
                this.profesores = result.data;
            } else {
                Avisos.error(result.error);
            }
        },
        
        nuevoProfesor() {
            this.formulario = {
                id: null,
                nombre: '',
                abreviatura: '',
                usuario: '',
                clave: '',
                email: '',
                telefono: '',
                idEspecialidad: '',
                observaciones: '',
                prefRojas: '',
                prefAmarillas: ''
            };
            this.contRojas = 0;
            this.modalInstance.show();
        },
        
        async editarProfesor(id) {
            const result = await ProfesoresAPI.obtener(id);
            if (result.success) {
                const prof = result.data;
                this.formulario = {
                    id: prof.id,
                    nombre: prof.nombre,
                    abreviatura: prof.abreviatura || '',
                    usuario: prof.usuario,
                    clave: '',
                    email: prof.email || '',
                    telefono: prof.telefono || '',
                    idEspecialidad: prof.idEspecialidad || '',
                    observaciones: prof.observaciones_horario || '',
                    prefRojas: '',
                    prefAmarillas: ''
                };
                
                // Cargar preferencias horarias
                await this.cargarPreferenciasHorarias(id);
                
                this.modalInstance.show();
            } else {
                Avisos.error(result.error);
            }
        },
        
        async cargarPreferenciasHorarias(idProfesor) {
            // En una implementación completa, esto cargaría las preferencias desde el backend
            // Por ahora, inicializamos vacías
            this.formulario.prefRojas = '';
            this.formulario.prefAmarillas = '';
            this.contRojas = 0;
        },
        
        obtenerClaseCelda(dia, hora) {
            const idCelda = dia + '_' + hora.replace(':', '_');
            if (this.formulario.prefRojas.includes(idCelda)) {
                return 'bg-danger text-white';
            } else if (this.formulario.prefAmarillas.includes(idCelda)) {
                return 'bg-warning text-dark';
            }
            return 'bg-light';
        },
        
        togglePreferencia(dia, hora) {
            const idCelda = dia + '_' + hora.replace(':', '_');
            
            // Si está roja, pasar a amarilla
            if (this.formulario.prefRojas.includes(idCelda)) {
                this.formulario.prefRojas = this.formulario.prefRojas.replace(idCelda, '');
                this.formulario.prefAmarillas += idCelda;
                this.contRojas--;
            }
            // Si está amarilla, quitar color
            else if (this.formulario.prefAmarillas.includes(idCelda)) {
                this.formulario.prefAmarillas = this.formulario.prefAmarillas.replace(idCelda, '');
            }
            // Si no tiene color, poner roja (si caben) o amarilla
            else {
                if (this.contRojas < this.maxRojas) {
                    this.formulario.prefRojas += idCelda;
                    this.contRojas++;
                } else {
                    this.formulario.prefAmarillas += idCelda;
                }
            }
        },
        
        async guardarProfesor() {
            const formData = new FormData();
            formData.append('id', this.formulario.id || '');
            formData.append('nombre', this.formulario.nombre);
            formData.append('abreviatura', this.formulario.abreviatura);
            formData.append('usuario', this.formulario.usuario);
            formData.append('clave', this.formulario.clave);
            formData.append('email', this.formulario.email);
            formData.append('telefono', this.formulario.telefono);
            formData.append('idEspecialidad', this.formulario.idEspecialidad);
            formData.append('observaciones', this.formulario.observaciones);
            formData.append('idDepartamento', this.idDepartamentoSeleccionado);
            formData.append('prefRojas', this.formulario.prefRojas);
            formData.append('prefAmarillas', this.formulario.prefAmarillas);
            
            const result = await ProfesoresAPI.guardar(formData);
            
            if (result.success) {
                this.modalInstance.hide();
                Avisos.exito('Éxito', result.data.mensaje);
                await this.cargarProfesores();
            } else {
                Avisos.error(result.error);
            }
        },
        
        async borrarProfesor(id, nombre) {
            const confirmed = await Avisos.confirmar('¿Borrar profesor?', 'Se eliminará al profesor "' + nombre + '" y todos sus vínculos (selección de materias, preferencias de horario...)', { boton: 'Sí, borrar' });
            
            if (confirmed.isConfirmed) {
                const result = await ProfesoresAPI.eliminar(id);
                
                if (result.success) {
                    Swal.fire('Eliminado', result.data.mensaje, 'success');
                    await this.cargarProfesores();
                } else {
                    Avisos.error(result.error);
                }
            }
        },
        
        async cambiarJefe(idProfesor) {
            const result = await ProfesoresAPI.actualizarJefe(idProfesor, this.idDepartamentoSeleccionado);
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Jefe actualizado',
                    timer: 1500,
                    showConfirmButton: false
                });
                await this.cargarProfesores();
            } else {
                Avisos.error(result.error);
            }
        },
        
        async cambiarActivo(idProfesor) {
            const result = await ProfesoresAPI.actualizarActivo(idProfesor);
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    timer: 1500,
                    showConfirmButton: false
                });
                await this.cargarProfesores();
            } else {
                Avisos.error(result.error);
            }
        }
    },
    
    watch: {
        idDepartamentoSeleccionado() {
            this.cargarProfesores();
        }
    }
};
