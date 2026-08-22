// Fase 7.1 — Histórico de selecciones
// Fiel a v3: muestra, por profesor, la selección actual, la seleccionada
// (confirmada) y la anterior de cada escenario, señalando conflictos.
const HistoricoView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-clock-history me-2"></i>Histórico de selecciones</h2>
                    <p class="text-muted">
                        <em>Selecciona un departamento y un escenario para ver el histórico de selecciones de sus profesores.</em>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cargar">
                        <option value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Escenario</label>
                    <select class="form-select" v-model="idEscenario" @change="cargar">
                        <option value="">-- Selecciona un escenario --</option>
                        <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                    </select>
                </div>
            </div>

            <div class="card shadow-sm" v-if="idDepartamento && idEscenario">
                <div class="card-body">
                    <div v-if="datos.length === 0" class="text-center text-muted py-4">
                        No hay datos.
                    </div>
                    <div v-for="p in datos" :key="p.idProfesor" class="card mb-3">
                        <div class="card-header">
                            <h5 class="h6 mb-0">{{ p.nombreProfesor }}<span v-if="p.conflicto" class="badge bg-danger ms-2">Conflicto</span></h5>
                        </div>
                        <div class="card-body">
                            <div v-for="s in p.seleccion" :key="s.id"
                                 class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                                <div class="flex-grow-1">{{ s.nombre }} ({{ s.abrevCurso }}{{ s.abrevGrupo }}, {{ s.horas }}h)</div>
                                <span v-if="s.conflicto" class="badge bg-warning">Doble</span>
                            </div>
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
            datos: []
        };
    },

    mounted() {
        this.cargarDepartamentos();
    },

    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            this.departamentos = await result.json();
        },

        async cargar() {
            if (!this.idDepartamento || !this.idEscenario) return;
            const res = await HistoricoAPI.listar(this.idDepartamento, this.idEscenario);
            if (res && res.success) this.datos = res.data;
        }
    }
};
