// Fase 4.2 — Competencias por Ciclo
// Las competencias se almacenan en competencias_ciclos, una fila por competencia
// (con su código, texto, tipo e id de ciclo).
//
// Fiel a v3 (competencias_ciclos.php + js/competencias_ciclos.js + modales/):
//   - se elige un ciclo y aparecen las competencias de ese ciclo;
//   - cada competencia se numera por su código, no por posición;
//   - se puede reordenar arrastrando (prefijo "cm" en el string de orden,
//     que el backend interpreta con substr($cod, 2));
//   - el modal de alta/edición pide código, texto y tipo
//     (1 = Profesional, 2 = Para la empleabilidad).
const CompetenciasCiclosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-trophy me-2"></i>Competencias por Ciclo</h2>
                    <p class="text-muted">
                        <em>Arrastra las competencias para ordenarlas entre sí. Haz clic en el icono del lápiz para editar los datos de cada competencia, y en el botón de «Nueva competencia» al final para añadir nuevas. Puedes eliminar competencias con el icono de borrar junto a cada apartado.</em>
                    </p>
                </div>
            </div>

            <!-- Selector de ciclo -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Ciclo</label>
                    <select class="form-select" v-model="idCicloSeleccionado" @change="cargar">
                        <option value="">-- Selecciona un ciclo --</option>
                        <option v-for="c in ciclos" :key="c.id" :value="c.id">{{ c.nombre }}</option>
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
                        <div v-for="(c, i) in competencias" :key="c.id"
                             draggable="true"
                             @dragstart="dragStart($event, c)"
                             @dragover.prevent
                             @drop="drop($event, i)"
                             class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2"
                             style="cursor: move">
                            <div class="flex-grow-1">{{ c.codigo }}. {{ c.texto }}</div>
                            <div class="d-flex gap-2">
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                            <div class="mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" v-model="form.tipo" required>
                                    <option value="" disabled>-- Selecciona un tipo --</option>
                                    <option value="1">Profesional</option>
                                    <option value="2">Para la empleabilidad</option>
                                </select>
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
            form: { id: 0, codigo: '', texto: '', tipo: '', idCiclo: '' },
            esEdicion: false,
            dragged: null
        };
    },

    mounted() {
        this.modal = new bootstrap.Modal(document.getElementById('modalCompetencia'));
        // Poblamos el desplegable de ciclos para poder elegir el ciclo
        this.cargarCiclos();
    },

    methods: {
        async cargar() {
            if (!this.idCicloSeleccionado) return;
            this.cargando = true;
            try {
                this.competencias = await CompetenciasCiclosAPI.listar(this.idCicloSeleccionado) || [];
            } catch (error) {
                Avisos.error(error.message);
                this.competencias = [];
            } finally {
                this.cargando = false;
            }
        },

        async cargarCiclos() {
            try {
                this.ciclos = await CompetenciasCiclosAPI.listar_ciclos() || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // Reordenar arrastrando (misma mecánica que v3: el string de orden
        // lleva el prefijo "cm" y cada id, separados por comas)
        dragStart(event, c) {
            this.dragged = c;
            event.dataTransfer.effectAllowed = 'move';
        },

        async drop(event, targetIndex) {
            if (!this.dragged) return;

            const draggedIndex = this.competencias.findIndex(c => c.id === this.dragged.id);
            if (draggedIndex === targetIndex) {
                this.dragged = null;
                return;
            }

            const item = this.competencias.splice(draggedIndex, 1)[0];
            this.competencias.splice(targetIndex, 0, item);

            const orden = this.competencias.map(c => 'cm' + c.id).join(',');
            try {
                await CompetenciasCiclosAPI.ordenar(orden);
                Avisos.exito('Orden actualizado');
            } catch (error) {
                Avisos.error(error.message);
                this.cargar();
            }

            this.dragged = null;
        },

        abrirModal(c) {
            this.form = { ...c };
            this.esEdicion = true;
            this.modal.show();
        },

        abrirNuevo() {
            this.form = { id: 0, codigo: '', texto: '', tipo: '', idCiclo: this.idCicloSeleccionado };
            this.esEdicion = false;
            this.modal.show();
        },

        async guardar() {
            if (!this.form.codigo || !this.form.texto || !this.form.tipo) {
                Avisos.aviso('Completa los campos obligatorios (código, texto y tipo)');
                return;
            }
            try {
                await CompetenciasCiclosAPI.guardar({
                    id: this.form.id,
                    codigo: this.form.codigo,
                    texto: this.form.texto,
                    tipo: this.form.tipo,
                    idCiclo: this.form.idCiclo || this.idCicloSeleccionado
                });
                Avisos.exito('Guardado');
                this.modal.hide();
                this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async eliminar(c) {
            const conf = await Avisos.confirmar('¿Eliminar competencia?', 'Confirmas el borrado de la competencia \'' + c.codigo + '\'?');
            if (conf.isConfirmed) {
                try {
                    await CompetenciasCiclosAPI.eliminar(c.id);
                    Avisos.exito('Eliminada');
                    this.cargar();
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        }
    }
};
