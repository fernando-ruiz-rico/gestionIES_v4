// Fase 7.1 — Histórico de selecciones de Desideratas
// Fiel a v3/historico.php: para cada profesor, qué eligió en el escenario,
// marcando en rojo las materias que tienen conflictos con otros profesores
const HistoricoView = {
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
                    <h2><i class="bi bi-clock-history me-2"></i>Histórico de selecciones</h2>
                    <p class="text-muted">
                        <em>Selecciona un escenario para ver el histórico de selecciones de los profesores del departamento.</em>
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

            <div v-if="idDepartamento && idEscenario">
                <p>
                    Listado de profesores/as con sus selecciones. Se muestran en
                    <span style="color: red">rojo</span> las materias que tienen conflictos con otros profesores/as.
                </p>
                <div v-for="p in datos" :key="p.id" class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <h5 class="h6 mb-0 fw-bold">{{ p.nombre }}</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="p.filas.length === 0" class="text-muted">
                            No hay selecciones.
                        </div>
                        <table class="table table-sm table-bordered" v-else>
                            <tr v-for="f in p.filas" :key="f.idSeleccion">
                                <td :style="f.conflicto ? 'color: red' : ''">{{ f.nombre }}</td>
                                <td class="text-center">{{ f.abrevCurso }}{{ f.mostrar ? ' ' + f.abrevGrupo : '' }}</td>
                                <td class="text-center">{{ f.horas }} h.</td>
                            </tr>
                        </table>
                        <div class="text-end"><strong>{{ p.total }} horas</strong></div>
                    </div>
                </div>
                <div v-if="datos.length === 0" class="text-center text-muted py-4">
                    No hay datos.
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
            datos: []
        };
    },

    computed: {
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        }
    },

    mounted() {
        // Si llegó desde «Selección» (botón «Vista previa») con parámetros, precargarlos
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
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            const data = await result.json();
            if (data.success) this.departamentos = data.data || [];
        },

        async cambiarDepartamento() {
            this.idEscenario = '';
            this.datos = [];
            this.cargarEscenarios();
        },

        async cargarEscenarios() {
            if (!this.idDepartamento) return;
            const res = await EscenariosAPI.listar(this.idDepartamento);
            if (res && res.success) this.escenarios = res.data || [];
        },

        async cargar() {
            if (!this.idDepartamento || !this.idEscenario) return;
            const res = await HistoricoAPI.listar(this.idDepartamento, this.idEscenario);
            if (res && res.success) this.datos = res.data || [];
        }
    }
};
