// Fase 7.2 — Estadísticas de horas
// Fiel a v3: muestra, por profesor, cuántas horas de las suyas tienen asignación
// directa, y cuántas de las suyas no tienen asignación directa.
const EstadisticasView = {
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
                    <h2><i class="bi bi-bar-chart me-2"></i>Estadísticas de horas</h2>
                    <p class="text-muted">
                        <em>Selecciona un departamento y un escenario para ver las estadísticas de horas de sus profesores.</em>
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

            <div v-if="idDepartamento && idEscenario">
                <div v-for="s in estadisticas" :key="s.idProfesor" class="card mb-3">
                    <div class="card-header">
                        <h5 class="h6 mb-0">{{ s.nombreProfesor }}</h5>
                    </div>
                    <div class="card-body d-flex">
                        <div class="me-4">Asignadas directamente: <strong>{{ s.asignadas_directas }}</strong>h</div>
                        <div class="me-4">No asignadas directamente: <strong>{{ s.solo_seleccion }}</strong>h</div>
                        <div>Total: <strong>{{ s.total }}</strong>h</div>
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
            estadisticas: []
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
            if (!this.idDepartamento) return;
            if (this.escenarios.length === 0) this.cargarEscenarios();
            if (!this.idEscenario) return;
            const res = await EstadisticasAPI.listar(this.idDepartamento, this.idEscenario);
            if (res && res.success) this.estadisticas = res.data;
        },

        cargarEscenarios() {
            const res = EscenariosAPI.listar();
            res.then(r => { if (r && r.success) this.escenarios = r.data; });
        }
    }
};
