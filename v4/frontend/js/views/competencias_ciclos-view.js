// Fase 4.2 — Competencias por Ciclo
// Las competencias se almacenan en competencias_ciclos, una fila por competencia
// (con su código, texto, tipo e id de ciclo).
const CompetenciasCiclosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-trophy me-2"></i>Competencias por Ciclo</h2>
                    <p class="text-muted">
                        <em>Selecciona un ciclo. Puedes editar cada competencia con el icono de lápiz, eliminarla con el icono de papelera, y añadir nuevas al final.</em>
                    </p>
                </div>
            </div>

            <!-- Selector de ciclo -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Ciclo</label>
                    <select class="form-select" v-model="idCicloSeleccionado" @change="cargar">
                        <option value="">-- Selecciona un ciclo --</option>
                        <option v-for="c in ciclos" :key="c.id" :value="c.id">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Listado de competencias -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div v-if="!idCicloSeleccionado" class="text-center text-muted py-4">
                        Selecciona un ciclo para ver sus competencias.
                    </div>
                    <div v-else-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div v-else-if="competencias.length === 0" class="text-center text-muted py-4">
                        Este ciclo no tiene competencias.
                    </div>
                    <div v-else>
                        <div class="listado claro" v-for="(c, i) in competencias" :key="c.id">
                            <div class="izquierda">{{ i+1 }}. {{ c.texto }}</div>
                            <div class="derecha">
                                <button class="btn btn-sm btn-outline-secondary" @click="abrirModal(c)" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para añadir competencia -->
            <div class="text-center mt-3" v-if="idCicloSeleccionado">
                <button class="btn btn-outline-primary" @click="abrirNuevo">
                    <i class="bi bi-plus-lg"></i>Nueva competencia
                </button>
            </div>

            <!-- Modal para editar/crear competencia -->
            <div class="modal fade" id="modalCompetencia" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar competencia' : 'Nueva competencia' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" class="form-control" v-model="form.codigo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Texto *</label>
                                <textarea class="form-control" rows="4" v-model="form.texto" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            ciclos: [],
            competencias: [],
            idCicloSeleccionado: '',
            cargando: false,
            form: { id: 0, codigo: '', texto: '', tipo: 1, idCiclo: '' },
            esEdicion: false
        };
    },

    mounted() {
        this.modal = new bootstrap.Modal(document.getElementById('modalCompetencia'));
    },

    methods: {
        async cargar() {
            if (!this.idCicloSeleccionado) return;
            this.cargando = true;
            try {
                const res = await CompetenciasCiclosAPI.listar(this.idCicloSeleccionado);
                if (res.success) this.competencias = res.data;
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        async cargarCiclos() {
            const res = await CompetenciasCiclosAPI.listar_ciclos();
            if (res.success) this.ciclos = res.data;
        },

        abrirModal(c) {
            this.form = { ...c };
            this.esEdicion = true;
            this.modal.show();
        },

        abrirNuevo() {
            this.form = { id: 0, codigo: '', texto: '', tipo: 1, idCiclo: this.idCicloSeleccionado };
            this.esEdicion = false;
            this.modal.show();
        },

        async guardar() {
            const res = await CompetenciasCiclosAPI.guardar({
                id: this.form.id,
                codigo: this.form.codigo,
                texto: this.form.texto,
                tipo: this.form.tipo,
                idCiclo: this.form.idCiclo || this.idCicloSeleccionado
            });
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false });
                this.modal.hide();
                this.cargar();
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        },

        async eliminar(c) {
            const conf = await Swal.fire({
                title: '¿Eliminar competencia?',
                text: c.texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (conf.isConfirmed) {
                const res = await CompetenciasCiclosAPI.eliminar(c.id);
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1500, showConfirmButton: false });
                    this.cargar();
                }
            }
        }
    }
};
