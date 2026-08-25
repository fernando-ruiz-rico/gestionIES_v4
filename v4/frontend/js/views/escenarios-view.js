// Vista de Escenarios de Desideratas
// Fiel a v3/cargar_escenarios.php: gestiona los escenarios de un departamento,
// sus departamentos asociados, y los estados "en vigor", "elegible en
// desideratas", "duplicar" y "modo rueda"
const EscenariosView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-signpost-split me-2"></i>Escenarios de Desideratas</h2>
                    <div class="d-flex gap-2">
                        <div v-if="esAdmin" class="d-flex align-items-center gap-2">
                            <label class="text-muted mb-0">Departamento</label>
                            <select class="form-select" style="max-width: 280px" v-model="idDepartamento" @change="cargar">
                                <option value="">-- Selecciona un departamento --</option>
                                <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" @click="abrirModal()">
                            <i class="bi bi-plus-lg"></i> Nuevo escenario
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Departamentos</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="e in escenarios" :key="e.id">
                                <td class="fw-bold">{{ e.nombre }}</td>
                                <td>
                                    <span v-for="d in e.departamentos" :key="d.id" class="badge bg-light text-dark border me-1">{{ d.nombre }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger me-1" title="Eliminar escenario" @click="eliminar(e)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary me-1" title="Editar nombre y departamentos" @click="editar(e)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button :class="['btn btn-sm me-1', e.actual ? 'btn-success' : 'btn-outline-secondary']"
                                            :title="e.actual ? 'Escenario actualmente en vigor' : 'Escenario antiguo o no en vigor'"
                                            @click="alternar(e, 'actual')">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button :class="['btn btn-sm me-1', e.activo_desideratas ? 'btn-danger' : 'btn-outline-danger']"
                                            :title="e.activo_desideratas ? 'Escenario activo para desideratas' : 'Escenario no elegible en desideratas'"
                                            @click="alternar(e, 'activo_desideratas')">
                                        <i :class="e.activo_desideratas ? 'bi bi-unlock' : 'bi bi-lock'"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary me-1" title="Duplicar este escenario" @click="duplicar(e)">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                    <button :class="['btn btn-sm', e.modo_rueda ? 'btn-success' : 'btn-outline-secondary']"
                                            :title="e.modo_rueda ? 'Escenario en modo rueda' : 'Modo rueda desactivado'"
                                            @click="alternar(e, 'modo_rueda')">
                                        <i class="bi bi-compass"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="escenarios.length === 0">
                                <td colspan="3" class="text-center text-muted py-4">Sin escenarios</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal de alta/edición -->
            <div class="modal fade" id="modalEscenario" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} escenario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" v-model="form.nombre">
                            </div>
                            <label class="form-label d-block">Departamentos</label>
                            <div class="mb-2">
                                <label v-for="d in departamentos" :key="d.id" class="form-check-label d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-2" :value="d.id" v-model="form.departamentos">
                                    {{ d.nombre }}
                                </label>
                            </div>
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
            departamentos: [],
            idDepartamento: '',
            escenarios: [],
            form: { id: 0, nombre: '', departamentos: [] },
            esEdicion: false,
            modal: null
        };
    },

    computed: {
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        }
    },

    mounted() {
        this.modal = new bootstrap.Modal(document.getElementById('modalEscenario'));
        this.cargarDepartamentos();
    },

    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            const data = await result.json();
            if (data.success) {
                this.departamentos = data.data || [];
                // Fiel a v3: el jefe de departamento solo gestiona su departamento
                if (!this.esAdmin && this.usuario && this.usuario.departamentoUsuario) {
                    this.idDepartamento = this.usuario.departamentoUsuario;
                }
                this.cargar();
            }
        },

        async cargar() {
            if (!this.idDepartamento) return;
            const result = await EscenariosAPI.listar(this.idDepartamento);
            if (result.success) {
                this.escenarios = result.data;
            } else {
                this.escenarios = [];
            }
        },

        abrirModal() {
            this.form = { id: 0, nombre: '', departamentos: [] };
            this.esEdicion = false;
            this.modal.show();
        },

        async editar(escenario) {
            const result = await EscenariosAPI.obtener(escenario.id);
            if (result.success) {
                this.form = {
                    id: result.data.id,
                    nombre: result.data.nombre,
                    departamentos: (result.data.departamentos || []).map(d => d.id)
                };
                this.esEdicion = true;
                this.modal.show();
            } else {
                Avisos.error(result.error);
            }
        },

        async guardar() {
            const result = await EscenariosAPI.guardar(this.form);
            if (result.success) {
                Avisos.exito('Éxito', 'Escenario guardado');
                this.modal.hide();
                this.cargar();
            } else {
                Avisos.error(result.error);
            }
        },

        eliminar(escenario) {
            Avisos.confirmar(
                '¿Eliminar escenario?',
                "Se eliminará el escenario '" + escenario.nombre + "' y todas sus selecciones.",
                { boton: '<i class="bi bi-trash me-2"></i>Sí, eliminar', confirmButtonColor: '#dc3545' }
            ).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await EscenariosAPI.eliminar(escenario.id);
                    if (result.success) {
                        Avisos.exito('Escenario eliminado');
                        this.cargar();
                    } else {
                        Avisos.error(result.error);
                    }
                }
            });
        },

        async alternar(escenario, campo) {
            const result = await EscenariosAPI.alternar(escenario.id, campo);
            if (result.success) {
                this.cargar();
            } else {
                Avisos.error(result.error);
            }
        },

        async duplicar(escenario) {
            const result = await EscenariosAPI.duplicar(escenario.id);
            if (result.success) {
                Avisos.exito('Escenario duplicado');
                this.cargar();
            } else {
                Avisos.error(result.error);
            }
        }
    }
};
