// Fase 7.2 — Estadísticas de la selección de Desideratas
// Fiel a v3/estadisticas.php: horas por especialidad, materias sin escoger,
// conflictos (sobredemanda, divisibilidad, pocas peticiones, mínimos y
// máximos de profesores) y el aviso a quien tiene conflictos propios
const EstadisticasView = {
    props: {
        usuario: {
            type: Object,
            required: true
        },
        params: {
            type: Object,
            default: () => ({})
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-bar-chart me-2"></i>Estadísticas</h2>
                    <p class="text-muted">
                        <em>Selecciona un escenario para ver las estadísticas y conflictos de la selección.</em>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4" v-if="esAdmin">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cambiarDepartamento">
                        <option value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Escenario</label>
                    <select class="form-select" v-model="idEscenario" @change="cargar" :disabled="!idDepartamento">
                        <option value="">-- Selecciona un escenario --</option>
                        <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                    </select>
                </div>
            </div>

            <div v-if="idDepartamento && idEscenario" class="row g-3">
                <!-- HORAS POR ESPECIALIDAD -->
                <div class="col-md-5">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="h6 mb-0">Horas por especialidades</h5></div>
                        <div class="card-body">
                            <div v-for="h in datos.horasPorEspecialidad" :key="h.id" class="mb-3">
                                <h6 class="h6">{{ h.descripcion }}</h6>
                                <div>Horas totales impartidas: <strong>{{ h.horasTotales }}</strong> / {{ h.horasRef }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONFLICTOS -->
                <div class="col-md-7">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="h6 mb-0">Conflictos</h5></div>
                        <div class="card-body">
                            <div v-if="!esAdmin" class="mb-3">
                                <div v-if="datos.tienesConflictos" class="alert alert-danger mb-0">
                                    Tienes conflictos. Revisa las materias seleccionadas.
                                </div>
                                <div v-else class="alert alert-success mb-0">
                                    No tienes conflictos.
                                </div>
                            </div>

                            <h6 class="h6">Materias sin escoger</h6>
                            <ul v-if="datos.noEscogidas.length > 0">
                                <li v-for="(m, i) in datos.noEscogidas" :key="i">
                                    <span v-if="m.especialidad">[{{ m.especialidad }}]</span>
                                    {{ m.nombre }} ({{ m.curso }} {{ m.grupo }}, {{ m.horas }}h)
                                </li>
                            </ul>
                            <div v-else class="text-muted">No hay materias sin escoger.</div>

                            <h6 class="h6 mt-3">Materias con conflictos</h6>
                            <ul v-if="datos.conflictos.length > 0">
                                <li v-for="(c, i) in datos.conflictos" :key="i" :class="{ 'fw-bold': c.tuyo }">{{ c.texto }}</li>
                            </ul>
                            <div v-else class="text-muted">No hay materias con conflictos.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            departamentos: [],
            idDepartamento: '',
            escenarios: [],
            idEscenario: '',
            datos: {
                horasPorEspecialidad: [],
                noEscogidas: [],
                conflictos: [],
                tienesConflictos: false
            }
        };
    },

    computed: {
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        }
    },

    mounted() {
        // Si llegó desde «Selección» (botón «Estadísticas») con parámetros, precargarlos
        if (this.params && this.params.idDepartamento && this.params.idEscenario) {
            this.idDepartamento = this.params.idDepartamento;
            this.idEscenario = this.params.idEscenario;
            this.cargarEscenarios();
            this.cargar();
            return;
        }
        if (this.esAdmin) {
            this.cargarDepartamentos();
        } else if (this.usuario && this.usuario.departamentoUsuario) {
            this.idDepartamento = this.usuario.departamentoUsuario;
            this.cargarEscenarios();
        }
    },

    methods: {
        async cargarDepartamentos() {
            try {
                this.departamentos = await DepartamentosAPI.listar() || [];
            } catch (error) {
                // Si falla, se mantiene el listado anterior
            }
        },

        async cambiarDepartamento() {
            this.idEscenario = '';
            this.datos = { horasPorEspecialidad: [], noEscogidas: [], conflictos: [], tienesConflictos: false };
            this.cargarEscenarios();
        },

        async cargarEscenarios() {
            if (!this.idDepartamento) return;
            try {
                this.escenarios = await EscenariosAPI.listar(this.idDepartamento) || [];
            } catch (error) {
                this.escenarios = [];
            }
        },

        async cargar() {
            if (!this.idDepartamento || !this.idEscenario) return;
            try {
                const data = await EstadisticasAPI.listar(this.idDepartamento, this.idEscenario);
                this.datos = data || this.datos;
            } catch (error) {
                // Si falla, se mantienen los datos anteriores
            }
        }
    }
};
