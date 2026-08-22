// Vista de Especialidades
//
// Fiel a v3 (especialidades.php + js/especialidades.js):
//  - El admin elige el departamento en un desplegable y ve/edita las
//    especialidades de ese departamento.
//  - El jefe de departamento no ve desplegable: ve directamente las
//    especialidades de su departamento (solo lectura: los endpoints de
//    guardar/eliminar son solo admin, igual que en v3).

const EspecialidadesView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-bookmark"></i> Especialidades</h2>
                    <button v-if="esAdmin" class="btn btn-primary" @click="abrirModalCrear()">
                        <i class="bi bi-plus-lg"></i> Nueva Especialidad
                    </button>
                </div>
            </div>

            <!-- Desplegable de departamentos: solo el admin lo ve. El jefe de
                 departamento ve siempre las especialidades de su departamento. -->
            <div class="row mb-3" v-if="esAdmin">
                <div class="col-auto">
                    <select class="form-select" style="max-width: 360px" v-model="deptoSeleccionado">
                        <option :value="0">--Selecciona un departamento--</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Departamento</th>
                                        <th v-if="esAdmin" class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="e in filas" :key="e.id">
                                        <td>{{ e.id }}</td>
                                        <td>{{ e.descripcion }}</td>
                                        <td>{{ e.departamento || '—' }}</td>
                                        <td v-if="esAdmin" class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(e)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(e)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!filas.length">
                                        <td :colspan="esAdmin ? 4 : 3" class="text-center text-muted py-4">
                                            {{ esAdmin && !deptoSeleccionado ? 'Selecciona un departamento para ver sus especialidades' : 'Sin especialidades' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal (solo el admin puede crear/editar) -->
            <div class="modal fade" id="modalEspecialidad" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nueva' }} Especialidad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Código (3 letras) *</label>
                                    <!-- El código es la PK; no se cambia al editar -->
                                    <input type="text" class="form-control" maxlength="3" v-model="form.id" :disabled="esEdicion" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Descripción *</label>
                                    <input type="text" class="form-control" v-model="form.descripcion" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Horas de tutoría (estimación)</label>
                                        <input type="number" min="0" class="form-control" v-model="form.horasTutoria">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Horas de inglés (estimación)</label>
                                        <input type="number" min="0" class="form-control" v-model="form.horasIngles">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <input type="text" class="form-control" :value="departamentoActualNombre" disabled>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">
                                {{ esEdicion ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            especialidades: [],
            departamentos: [],
            deptoSeleccionado: 0,
            form: { id: '', descripcion: '', idDepartamento: 0, horasTutoria: '', horasIngles: '' },
            esEdicion: false,
            modal: null
        };
    },

    computed: {
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        },

        // Departamento actual: para el admin es el del desplegable; para el
        // jefe de departamento, fijo al suyo
        deptoActual() {
            if (this.esAdmin) return this.deptoSeleccionado;
            return this.usuario ? (this.usuario.departamentoUsuario || 0) : 0;
        },

        filas() {
            if (!this.deptoActual) return [];
            return this.especialidades.filter(e => e.idDepartamento == this.deptoActual);
        },

        departamentoActualNombre() {
            const d = this.departamentos.find(dep => dep.id == this.deptoActual);
            return d ? d.nombre : '—';
        }
    },

    mounted() {
        this.cargar();
        this.cargarDepartamentos();
        this.modal = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
    },

    methods: {
        async cargar() {
            const result = await EspecialidadesAPI.listar();
            if (result.success) {
                this.especialidades = result.data;
            } else {
                this.especialidades = [];
            }
        },

        async cargarDepartamentos() {
            try {
                const response = await fetch('../backend/api/departamentos/listar.php', { credentials: 'include' });
                const data = await response.json();
                this.departamentos = Array.isArray(data) ? data : [];
            } catch (e) {
                console.error(e);
                this.departamentos = [];
            }
        },

        abrirModalCrear() {
            if (!this.deptoActual) {
                Avisos.error('Debes seleccionar un departamento');
                return;
            }

            this.form = { id: '', descripcion: '', idDepartamento: this.deptoActual, horasTutoria: '', horasIngles: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(especialidad) {
            this.form = {
                id: especialidad.id,
                descripcion: especialidad.descripcion,
                idDepartamento: especialidad.idDepartamento,
                horasTutoria: especialidad.horasTutoria,
                horasIngles: especialidad.horasIngles
            };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await EspecialidadesAPI.guardar(this.form);

            if (result.success) {
                Avisos.exito('Éxito', result.message);

                this.modal.hide();
                this.cargar();
            } else {
                Avisos.error(result.error);
            }
        },

        eliminar(especialidad) {
            Avisos.confirmar('¿Eliminar especialidad?', especialidad.descripcion).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await EspecialidadesAPI.eliminar(especialidad.id);

                    if (result.success) {
                        Avisos.exito();
                        this.cargar();
                    } else {
                        Avisos.error(result.error);
                    }
                }
            });
        }
    }
};
