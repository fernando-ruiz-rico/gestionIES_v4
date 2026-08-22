// Fase 7.4 — Exportación a Excel
// Fiel a v3: muestra los datos de cursos, materias y selecciones listos para
// descargar como hoja de cálculo (en v3 se descargaba directamente un .xlsx).
const ExcelView = {
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
                    <h2><i class="bi bi-file-earmark-excel me-2"></i>Exportación a Excel</h2>
                    <p class="text-muted">
                        <em>Muestra los datos de cursos, materias y selecciones para exportarlos a una hoja de cálculo.</em>
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

            <div class="text-center mb-3">
                <button class="btn btn-success" @click="descargar" v-if="datos">
                    <i class="bi bi-download me-1"></i>Descargar
                </button>
            </div>

            <div v-if="cargando" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div v-else-if="datos">
                <h5>Cursos ({{ datos.cursos.length }})</h5>
                <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Abrev.</th></tr></thead>
                    <tbody>
                        <tr v-for="c in datos.cursos" :key="'c'+c.id">
                            <td>{{ c.id }}</td><td>{{ c.codigo }}</td><td>{{ c.nombre }}</td><td>{{ c.abreviatura }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <h5>Materias ({{ datos.materias.length }})</h5>
                <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Curso</th></tr></thead>
                    <tbody>
                        <tr v-for="m in datos.materias" :key="'m'+m.id">
                            <td>{{ m.id }}</td><td>{{ m.codigo }}</td><td>{{ m.nombre }}</td><td>{{ m.idCurso }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <h5>Selecciones ({{ datos.selecciones.length }})</h5>
                <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Profesor</th><th>Materia</th><th>Curso</th><th>Grupo</th><th>Horas</th></tr></thead>
                    <tbody>
                        <tr v-for="s in datos.selecciones" :key="'s'+s.id">
                            <td>{{ s.nombreProfesor }}</td><td>{{ s.nombreMateria }}</td><td>{{ s.abrevCurso }}</td><td>{{ s.abrevGrupo }}</td><td>{{ s.horas }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            departamentos: [],
            escenarios: [],
            idDepartamento: '',
            idEscenario: '',
            datos: null,
            cargando: false
        };
    },

    mounted() {
        this.cargarDepartamentos();
    },

    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            this.departamentos = await result.json();
            const res = await EscenariosAPI.listar();
            if (res.success) this.escenarios = res.data || [];
        },

        async cargar() {
            if (!this.idDepartamento || !this.idEscenario) { this.datos = null; return; }
            this.cargando = true;
            try {
                const res = await fetch('../backend/api/excel.php?action=listar&idDepartamento=' + this.idDepartamento + '&idEscenario=' + this.idEscenario, { credentials: 'include' });
                const json = await res.json();
                if (json.success) this.datos = json.data;
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        descargar() {
            if (!this.datos) return;
            // Genera un CSV con los datos y lo descarga
            let csv = 'tipo;id;detalle;detalle2;detalle3\r\n';
            this.datos.cursos.forEach(c => { csv += 'curso;'+c.id+';'+c.codigo+';'+c.nombre+';'+c.abreviatura+'\r\n'; });
            this.datos.materias.forEach(m => { csv += 'materia;'+m.id+';'+m.codigo+';'+m.nombre+';'+m.idCurso+'\r\n'; });
            this.datos.selecciones.forEach(s => { csv += 'seleccion;'+s.id+';'+s.nombreProfesor+';'+s.nombreMateria+';'+s.horas+'\r\n'; });
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'exportacion.xlsx';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }
};
