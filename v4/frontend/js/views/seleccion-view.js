// Fase 5.1 — Selección de materias
// Fiel a v3: la selección de materias se guarda en la tabla seleccion, una fila
// por cada elección que hace un profesor sobre una materia para un grupo y un
// escenario (desiderata) concretos.
const SeleccionView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-list-check me-2"></i>Selección de materias</h2>
                    <p class="text-muted">
                        <em>Selecciona un departamento, un escenario y un profesor (si eres admin). Haz clic en el botón '+' de una materia para añadirla a su selección.</em>
                    </p>
                </div>
            </div>

            <!-- Selectores -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cargarBase">
                        <option value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Escenario</label>
                    <select class="form-select" v-model="idEscenario" @change="cargarSeleccion">
                        <option value="">-- Selecciona un escenario --</option>
                        <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-4" v-if="permisosSuper">
                    <label class="form-label">Profesor</label>
                    <select class="form-select" v-model="idProfesor" @change="cargarSeleccion">
                        <option value="">-- Selecciona un profesor --</option>
                        <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                    </select>
                </div>
            </div>

            <div v-if="!idDepartamento" class="text-center text-muted py-4">
                Selecciona un departamento para empezar.
            </div>

            <div v-else class="row">
                <!-- Panel de materias -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header"><h5 class="h6 mb-0"><i class="bi bi-book me-2"></i>Materias</h5></div>
                        <div class="card-body">
                            <div v-for="m in materias" :key="m.idMateria"
                                 class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                                <div class="flex-grow-1">{{ m.nombre }}<em v-if="m.tipo === 'TUTORIA'"> (Tutoría)</em></div>
                                <button class="btn btn-sm btn-outline-primary" @click="insertarSeleccion(m)" title="Añadir a la selección">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de selección -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header"><h5 class="h6 mb-0"><i class="bi bi-check2-square me-2"></i>Selección</h5></div>
                        <div class="card-body">
                            <div v-if="selecciones.length === 0" class="text-muted">
                                No hay selecciones.
                            </div>
                            <div v-else>
                                <div v-for="(s, i) in selecciones" :key="s.id"
                                     class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                                    <div class="flex-grow-1">{{ i+1 }}. {{ s.nombre }} ({{ s.abrevCurso }}{{ s.abrevGrupo }}, {{ s.horas }}h)</div>
                                    <button class="btn btn-sm btn-outline-danger" @click="borrarSeleccion(s)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="mt-3" v-if="totalHoras">
                                    <span class="badge bg-primary">Total: {{ totalHoras }}h</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
            departamentos: [],
            idDepartamento: '',
            escenarios: [],
            idEscenario: '',
            profesores: [],
            idProfesor: '',
            permisosSuper: false,
            materias: [],
            selecciones: []
        };
    },

    computed: {
        totalHoras() {
            return this.selecciones.reduce((sum, s) => sum + parseInt(s.horas || 0), 0);
        }
    },

    mounted() {
        this.cargarDepartamentos();
    },

    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            this.departamentos = await result.json();
        },

        async cargarBase() {
            if (!this.idDepartamento) return;
            // Cargamos escenarios (son globales en v4)
            const resEsc = await EscenariosAPI.listar();
            if (resEsc.success) this.escenarios = resEsc.data || [];
            // Cargamos profesores
            const resProf = await SeleccionAPI.listar_profesores(this.idDepartamento, '');
            if (resProf.success) {
                this.profesores = resProf.data;
                // Si el usuario no es admin/jefe, se selecciona a sí mismo
                if (!this.permisosSuper && this.usuarioEsProfesor()) {
                    this.idProfesor = this.usuario ? this.usuario.idUsuario : 0;
                }
            }
            // Cargamos materias
            const resMaterias = await this.cargarMaterias();
            this.cargarSeleccion();
        },

        async cargarMaterias() {
            const res = await SeleccionAPI.listar_materias(this.idDepartamento);
            if (res && res.success) this.materias = res.data;
            return res;
        },

        async cargarSeleccion() {
            if (!this.idProfesor || !this.idEscenario) return;
            const res = await SeleccionAPI.listar_seleccion(this.idProfesor, this.idEscenario);
            if (res && res.success) this.selecciones = res.data;
        },

        usuarioEsProfesor() {
            return this.usuario && this.usuario.rol === 'profesor';
        },

        insertarSeleccion(m) {
            const idGrupo = 0; // Se deja sin grupo por simplicidad; el backend usa idGrupo
            const data = {
                idProfesor: this.idProfesor,
                idMateria: m.idMateria,
                idGrupo: 0,
                idEscenario: this.idEscenario,
                horas: m.horas || 3
            };
            SeleccionAPI.insertar_seleccion(data).then(res => {
                if (res && res.success) {
                    this.cargarSeleccion();
                } else if (res && res.error) {
                    Swal.fire('Error', res.error, 'error');
                }
            });
        },

        borrarSeleccion(s) {
            SeleccionAPI.borrar_seleccion(s.id).then(res => {
                if (res && res.success) {
                    this.cargarSeleccion();
                }
            });
        }
    }
};
