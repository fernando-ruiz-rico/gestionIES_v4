// Componente Perfil View (menú «Perfil» de v3: los datos del propio profesor)
//
// Fiel a v3 (menú "Perfil" de includes/config.php + modales/profesores.php +
// cargarPerfil de js/main.js): solo lo ven profesor y jefe de departamento
// (los roles que define el menú en backend/config.php). El formulario carga
// los datos del usuario de la sesión —la clave siempre vacía, como en v3:
// si no se rellena se conserva la actual— y la abreviatura queda en
// solo-lectura (v3: cargarPerfil(..., editarAbreviatura = false)). Las horas
// de la rejilla de preferencias salen de la tabla "horas" (no van duras) y
// se pinta ya marcada lo guardado; al guardar se mandan las mismas cadenas
// de códigos que v3 (día + hora con '_' en vez de ':', p. ej. L07_55).
const PerfilView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-3">
                        <i class="bi bi-person-circle me-2"></i>Perfil
                    </h1>
                    <p class="text-muted">
                        <em>
                            Editas aquí tus datos de profesor. Si no quieres cambiar la
                            contraseña, deja el campo de clave vacío. La abreviatura del
                            nombre no se puede cambiar desde aquí: la gestiona el
                            administrador en el módulo «Profesores».
                        </em>
                    </p>
                </div>
            </div>

            <!-- Cargando -->
            <div class="card shadow-sm mb-4" v-if="cargando">
                <div class="card-body text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>

            <!-- El admin no ve esta opción en el menú; si llega aquí, se lo decimos -->
            <div class="card shadow-sm mb-4" v-else-if="esAdmin">
                <div class="card-body p-4">
                    <p class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Esta opción no está disponible para el rol de administrador:
                        gestiona a los profesores desde el módulo «Profesores».
                    </p>
                </div>
            </div>

            <div v-else class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 h6"><i class="bi bi-person me-2"></i>Formulario de perfil de profesor</h5>
                </div>
                <div class="card-body">
                    <form id="formperfil" @submit.prevent="guardarPerfil">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="nombrePerfil">Nombre</label>
                                    <input class="form-control" type="text" id="nombrePerfil" v-model="formulario.nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="abreviaturaPerfil">Abreviatura del nombre</label>
                                    <input class="form-control bg-light" type="text" id="abreviaturaPerfil" v-model="formulario.abreviatura" readonly>
                                    <small class="text-muted">Solo el administrador puede cambiarla (módulo «Profesores»).</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="usuarioPerfil">Login de usuario</label>
                                    <input class="form-control" type="text" id="usuarioPerfil" v-model="formulario.usuario" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="clavePerfil">Clave</label>
                                    <input class="form-control" type="password" id="clavePerfil" v-model="formulario.clave" placeholder="Dejar vacío si no se cambia">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="emailPerfil">E-mail</label>
                                    <input class="form-control" type="email" id="emailPerfil" v-model="formulario.email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="telefonoPerfil">Teléfono</label>
                                    <input class="form-control" type="text" id="telefonoPerfil" v-model="formulario.telefono">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="idEspecialidadPerfil">Especialidad</label>
                                    <select class="form-select" id="idEspecialidadPerfil" v-model="formulario.idEspecialidad" required>
                                        <option value="">-- Seleccionar especialidad --</option>
                                        <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">{{ esp.descripcion }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="observacionesPerfil">Observaciones referentes al horario</label>
                                    <textarea class="form-control" rows="5" id="observacionesPerfil" v-model="formulario.observaciones"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p class="fw-bold">
                                    Preferencias de horario
                                    <span class="badge bg-warning text-dark" title="Deja en rojo las casillas donde no quieras tener clase, y en amarillo donde preferirías no tener clase. Cambia el color de las casillas haciendo clic sobre ellas.">?</span>
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th v-for="dia in dias" :key="dia">{{ dia }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="hora in horasManana" :key="'M' + hora">
                                                <th>{{ hora }}</th>
                                                <td v-for="dia in dias"
                                                    :key="dia + hora"
                                                    :class="obtenerClaseCelda(dia, hora)"
                                                    @click="togglePreferencia(dia, hora)"></td>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="bg-light">&nbsp;</th>
                                            </tr>
                                            <tr v-for="hora in horasTarde" :key="'T' + hora">
                                                <th>{{ hora }}</th>
                                                <td v-for="dia in dias"
                                                    :key="dia + hora"
                                                    :class="obtenerClaseCelda(dia, hora)"
                                                    @click="togglePreferencia(dia, hora)"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    Máximo <strong>{{ maxRojas }}</strong> casillas rojas (actualmente {{ contRojas }})
                                </small>
                            </div>
                        </div>
                        <div class="d-md-flex justify-content-md-end mt-3">
                            <button class="btn btn-primary" type="submit" :disabled="guardando">
                                <i class="bi bi-check-lg me-1"></i>Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `,

    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            cargando: true,
            guardando: false,
            esAdmin: this.usuario.rol === 'admin',
            especialidades: [],
            // Rejilla de preferencias: horas de la tabla "horas" (no duras,
            // como en v3) y casillas ya guardadas del profesor
            dias: ['L', 'M', 'X', 'J', 'V'],
            horasManana: [],
            horasTarde: [],
            maxRojas: 3,
            contRojas: 0,
            formulario: {
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
            }
        };
    },

    methods: {
        // Código de celda, el mismo que v3: día + hora con '_' en vez de
        // ':' (p. ej. L07_55), siempre de 6 caracteres.
        codigoCelda(dia, hora) {
            return dia + hora.replace(':', '_');
        },

        obtenerClaseCelda(dia, hora) {
            const idCelda = this.codigoCelda(dia, hora);
            if (this.formulario.prefRojas.includes(idCelda)) {
                return 'text-bg-danger';
            } else if (this.formulario.prefAmarillas.includes(idCelda)) {
                return 'text-bg-warning';
            }
            return 'bg-light';
        },

        togglePreferencia(dia, hora) {
            const idCelda = this.codigoCelda(dia, hora);

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

        async guardarPerfil() {
            if (!this.formulario.nombre) {
                Avisos.aviso('El nombre es obligatorio');
                return;
            }
            if (!this.formulario.idEspecialidad) {
                Avisos.aviso('La especialidad es obligatoria');
                return;
            }

            this.guardando = true;
            try {
                // id del propio profesor de la sesión; la clave, si llega
                // vacía, no se toca en el backend (se conserva la actual)
                const result = await ProfesoresAPI.guardar({
                    id: this.usuario.idUsuario,
                    idDepartamento: this.usuario.idDepartamento,
                    nombre: this.formulario.nombre,
                    abreviatura: this.formulario.abreviatura,
                    usuario: this.formulario.usuario,
                    clave: this.formulario.clave,
                    email: this.formulario.email,
                    telefono: this.formulario.telefono,
                    idEspecialidad: this.formulario.idEspecialidad,
                    observaciones: this.formulario.observaciones,
                    prefRojas: this.formulario.prefRojas,
                    prefAmarillas: this.formulario.prefAmarillas
                });
                Avisos.exito('Operación realizada correctamente', result.data.mensaje);
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.guardando = false;
            }
        }
    },

    async mounted() {
        if (this.esAdmin) {
            this.cargando = false;
            return;
        }

        try {
            // Datos del profesor de la sesión; la clave siempre vacía,
            // como en v3 (cargarPerfil deja '#clavePerfil' a vacío)
            const prof = await ProfesoresAPI.obtener(this.usuario.idUsuario);
            this.formulario.nombre = prof.nombre;
            this.formulario.abreviatura = prof.abreviatura || '';
            this.formulario.usuario = prof.usuario;
            this.formulario.email = prof.email || '';
            this.formulario.telefono = prof.telefono || '';
            this.formulario.idEspecialidad = prof.idEspecialidad || '';
            this.formulario.observaciones = prof.observaciones_horario || '';

            // Solo las especialidades de su departamento, como en v3
            const todas = await EspecialidadesAPI.listar() || [];
            this.especialidades = todas.filter(e => e.idDepartamento == this.usuario.idDepartamento);

            // Rejilla: horas de la tabla "horas" + casillas ya guardadas
            const prefs = await ProfesoresAPI.preferencias(this.usuario.idUsuario);
            this.horasManana = prefs.horasManana || [];
            this.horasTarde = prefs.horasTarde || [];
            this.formulario.prefRojas = prefs.rojas || '';
            this.formulario.prefAmarillas = prefs.amarillas || '';
            this.contRojas = Math.floor(this.formulario.prefRojas.length / 6);
        } catch (error) {
            Avisos.error(error.message);
        } finally {
            this.cargando = false;
        }
    }
};
