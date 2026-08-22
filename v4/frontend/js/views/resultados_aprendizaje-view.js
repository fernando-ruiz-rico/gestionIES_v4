// Fase 4.1 — Resultados de Aprendizaje (RA) por materia
// Los RA se asocian a cada materia, con % de atención en empresa y % de evaluación,
// y pueden llevar asociados criterios de evaluación (CE).
const ResultadosArendizajeView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-book me-2"></i>Resultados de Aprendizaje</h2>
                    <p class="text-muted">
                        <em>Selecciona una materia. Haz clic en el lápiz para editar cada resultado, en el icono de árbol para asociar criterios de evaluación, y en el icono de medalla para fijar qué resultados son clave y qué porcentaje de evaluación tienen.</em>
                    </p>
                </div>
            </div>

            <!-- Selector de materias -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Materia</label>
                    <select class="form-select" v-model="idMateriaSeleccionada" @change="cargar">
                        <option value="">-- Selecciona una materia --</option>
                        <option v-for="m in materias" :key="m.idMateria" :value="m.idMateria">
                            {{ m.nombre }} ({{ m.curso }})
                        </option>
                    </select>
                </div>
                <div class="col-md-8" v-if="permisos">
                    <div class="form-inline">
                        <label class="form-label me-2">Horas a impartir en empresa:</label>
                        <input type="number" class="form-control" style="width:100px" v-model="horasEmpresa">
                        <button class="btn btn-outline-primary ms-2" @click="actualizarHoras">Actualizar</button>
                    </div>
                </div>
            </div>

            <!-- Listado de RA -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div v-if="!idMateriaSeleccionada" class="text-center text-muted py-4">
                        Selecciona una materia para ver sus resultados de aprendizaje.
                    </div>
                    <div v-else-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div v-else-if="resultados.length === 0" class="text-center text-muted py-4">
                        esta materia no tiene resultados de aprendizaje.
                    </div>
                    <div v-else>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2" v-for="r in resultados" :key="r.id">
                            <div class="flex-grow-1">
                                {{ r.orden }}. {{ r.texto }}
                                <em v-if="r.porcentaje_empresa"> ({{ r.porcentaje_empresa }}% empresa)</em>
                                <span v-if="r.es_clave" class="badge bg-success ms-2" title="RA clave">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" @click="abrirEvaluar(r)" title="Asociar criterios de evaluación">
                                    <i class="bi bi-tree"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" @click="abrirModal(r)" title="Editar resultado">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" @click="abrirCriterios(r)" title="Ver criterios de evaluación">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" v-if="permisos" @click="eliminar(r)" title="Eliminar resultado">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para añadir RA (solo admins/jefes) -->
            <div class="text-center mt-3" v-if="permisos && idMateriaSeleccionada">
                <button class="btn btn-outline-primary" @click="abrirNuevo">
                    <i class="bi bi-plus-lg"></i>Nuevo resultado
                </button>
            </div>

            <!-- Modal para editar/crear un RA -->
            <div class="modal fade" id="modalRA" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Resultado a editar' : 'Nuevo resultado' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Resultado *</label>
                                <textarea class="form-control" rows="3" v-model="form.texto" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Orden</label>
                                <input type="number" class="form-control" v-model="form.orden">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">% a impartir en empresa</label>
                                <input type="number" class="form-control" v-model="form.porcentaje_empresa">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para evaluar un RA (CE clave y % evaluación) -->
            <div class="modal fade" id="modalEvaluar" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Evaluar resultado</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Porcentaje de evaluación</label>
                                <input type="number" class="form-control" v-model="evalForm.porcentaje_evaluacion">
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="esClave" v-model="evalForm.es_clave">
                                <label class="form-check-label" for="esClave">Es un resultado clave</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="actualizarEvaluar">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para criterios de evaluación -->
            <div class="modal fade" id="modalCriterios" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Criterios de evaluación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-for="c in criterios" :key="'cce'+c.codigo"
                                 class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                                <div class="flex-grow-1">{{ c.codigo }}. {{ c.texto }}</div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" @click="actualizarCriterio(c)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" @click="eliminarCriterio(c)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-2"><input type="text" class="form-control" v-model="nuevoCriterio.codigo" placeholder="Código"></div>
                                <div class="col-7"><input type="text" class="form-control" v-model="nuevoCriterio.texto" placeholder="Texto"></div>
                                <div class="col-1"><button class="btn btn-success" @click="guardarCriterio"><i class="bi bi-plus"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            materias: [],
            resultados: [],
            idMateriaSeleccionada: '',
            cargando: false,
            permisos: false,
            horasEmpresa: 0,
            form: { id: 0, texto: '', orden: 1, porcentaje_empresa: 0 },
            esEdicion: false,
            evalForm: { idResultado: 0, porcentaje_evaluacion: 0, es_clave: true },
            criterios: [],
            nuevoCriterio: { codigo: '', texto: '' },
            idRAActual: 0
        };
    },

    mounted() {
        this.modalRA = new bootstrap.Modal(document.getElementById('modalRA'));
        this.modalEvaluar = new bootstrap.Modal(document.getElementById('modalEvaluar'));
        this.modalCriterios = new bootstrap.Modal(document.getElementById('modalCriterios'));
    },

    methods: {
        async cargar() {
            if (!this.idMateriaSeleccionada) return;
            this.cargando = true;
            try {
                const res = await ResultadosArendizajeAPI.listar_materias();
                if (res.success) this.materias = res.data;
                const data = await ResultadosArendizajeAPI.cargar(this.idMateriaSeleccionada);
                if (data.success) {
                    this.resultados = data.data.resultados;
                    this.permisos = data.data.permisos;
                    this.horasEmpresa = data.data.horas_empresa;
                } else {
                    this.resultados = [];
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        actualizarHoras() {
            if (!this.idMateriaSeleccionada) return;
            const result = ResultadosArendizajeAPI.actualizar_horas({
                idMateria: this.idMateriaSeleccionada,
                horas: this.horasEmpresa
            });
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Actualizado', timer: 1500, showConfirmButton: false });
            }
        },

        abrirModal(r) {
            this.form = { ...r };
            this.esEdicion = true;
            this.modalRA.show();
        },

        abrirNuevo() {
            this.form = { id: 0, texto: '', orden: this.resultados.length + 1, porcentaje_empresa: 0 };
            this.esEdicion = false;
            this.modalRA.show();
        },

        guardar() {
            const result = ResultadosArendizajeAPI.guardar({
                id: this.form.id,
                idMateria: this.idMateriaSeleccionada,
                texto: this.form.texto,
                orden: this.form.orden,
                porcentaje_empresa: this.form.porcentaje_empresa
            });
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false });
                this.modalRA.hide();
                this.cargar();
            } else {
                Swal.fire('Error', result.error, 'error');
            }
        },

        abrirEvaluar(r) {
            this.evalForm = { idResultado: r.id, porcentaje_evaluacion: r.porcentaje_evaluacion, es_clave: r.es_clave };
            this.modalEvaluar.show();
        },

        actualizarEvaluar() {
            const result = ResultadosArendizajeAPI.actualizar_evaluacion(this.evalForm);
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Evaluación actualizada', timer: 1500, showConfirmButton: false });
            }
        },

        async abrirCriterios(r) {
            this.idRAActual = r.id;
            await this.cargarCriterios();
            this.modalCriterios.show();
        },

        async cargarCriterios() {
            const res = await ResultadosArendizajeAPI.cargar_criterios(this.idRAActual);
            if (res.success) this.criterios = res.data;
        },

        async eliminar(r) {
            const conf = await Swal.fire({
                title: '¿Eliminar resultado?',
                text: r.texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (conf.isConfirmed) {
                const result = ResultadosArendizajeAPI.eliminar(r.id);
                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
                    this.cargar();
                }
            }
        },

        async guardarCriterio() {
            const result = await ResultadosArendizajeAPI.guardar_criterio({
                idResultado: this.idRAActual,
                codigo: this.nuevoCriterio.codigo,
                texto: this.nuevoCriterio.texto
            });
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Criterio guardado', timer: 1500, showConfirmButton: false });
                this.cargarCriterios();
            }
        },

        async actualizarCriterio(c) {
            const result = ResultadosArendizajeAPI.actualizar_criterio({
                idResultado: c.idRA,
                codigo: c.codigo,
                nuevoCodigo: c.codigo,
                nuevoTexto: c.texto
            });
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Criterio actualizado', timer: 1500, showConfirmButton: false });
                this.cargarCriterios();
            }
        },

        async eliminarCriterio(c) {
            const result = ResultadosArendizajeAPI.eliminar_criterio({
                idResultado: c.idRA,
                codigo: c.codigo
            });
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Criterio eliminado', timer: 1500, showConfirmButton: false });
                this.cargarCriterios();
            }
        }
    }
};
