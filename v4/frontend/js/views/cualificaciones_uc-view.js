// Fase 4.3 — Cualificaciones profesionales y Unidades de Competencia
// Las cualificaciones (cualificaciones_profesionales) pueden asociar unidades de
// competencia (unidades_competencia) a través de cualificaciones_unidades.
const CualificacionesUCView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-briefcase me-2"></i>Cualificaciones Profesionales</h2>
                    <p class="text-muted">
                        <em>Gestiona las cualificaciones profesionales y sus unidades de competencia. Puedes editar con el lápiz, eliminar con la papelera, y asociar/desasociar unidades con el icono de árbol.</em>
                    </p>
                </div>
            </div>

            <div class="row">
                <!-- Panel izquierdo: cualificaciones -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><h5 class="h6 mb-0"><i class="bi bi-briefcase me-2"></i>Cualificaciones</h5></div>
                        <div class="card-body">
                            <div v-for="q in cualificaciones" :key="q.codigo" class="listado claro">
                                <div class="izquierda">{{ q.codigo }} — {{ q.texto }}</div>
                                <div class="derecha">
                                    <button class="btn btn-sm btn-outline-secondary" @click="abrirAsociar(q)" title="Asociar unidades">
                                        <i class="bi bi-tree"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" @click="eliminarCualificacion(q)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" @click="abrirModal(q)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" v-model="nuevaCualificacion.codigo" placeholder="Código">
                                <input type="text" class="form-control me-2" v-model="nuevaCualificacion.texto" placeholder="Texto">
                                <button class="btn btn-success" @click="guardarCualificacion"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel derecho: unidades de competencia -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><h5 class="h6 mb-0"><i class="bi bi-list-check me-2"></i>Unidades de Competencia</h5></div>
                        <div class="card-body">
                            <div v-for="u in unidades" :key="u.codigo" class="listado claro">
                                <div class="izquierda">{{ u.codigo }} — {{ u.texto }}</div>
                                <div class="derecha">
                                    <button class="btn btn-sm btn-outline-danger" @click="eliminarUnidad(u)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" @click="abrirUnidadModal(u)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" v-model="nuevaUnidad.codigo" placeholder="Código">
                                <input type="text" class="form-control me-2" v-model="nuevaUnidad.texto" placeholder="Texto">
                                <button class="btn btn-success" @click="guardarUnidad"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal asociar unidades -->
            <div class="modal fade" id="modalAsociar" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Asociar unidades a <strong>{{ cualificacionActual.codigo }}</strong></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="asociaciones.length === 0" class="text-muted">
                                No hay unidades asociadas.
                            </div>
                            <div v-else class="listado claro">
                                <div v-for="a in asociaciones" :key="a.codigoUnidad" class="izquierda">
                                    {{ a.codigoUnidad }} — {{ a.texto }}
                                    <button class="btn btn-sm btn-outline-danger float-end" @click="eliminarAsociacion(a)" title="Desasociar">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <label class="form-label">Añadir nueva unidad:</label>
                            <select class="form-select mb-2" v-model="unidadNueva" @change="asociar">
                                <option value="">-- Selecciona una unidad --</option>
                                <option v-for="u in unidadesNoAsociadas" :key="u.codigo" :value="u.codigo">{{ u.codigo }} — {{ u.texto }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal editar cualificacion -->
            <div class="modal fade" id="modalCualificacion" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicionC ? 'Editar cualificación' : 'Nueva cualificación' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" class="form-control" v-model="formCualificacion.codigo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Texto *</label>
                                <textarea class="form-control" v-model="formCualificacion.texto" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardarCualificacionModal">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal editar unidad -->
            <div class="modal fade" id="modalUnidad" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicionU ? 'Editar unidad' : 'Nueva unidad' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" class="form-control" v-model="formUnidad.codigo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Texto *</label>
                                <textarea class="form-control" v-model="formUnidad.texto" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardarUnidadModal">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            cualificaciones: [],
            unidades: [],
            asociaciones: [],
            cualificacionActual: {},
            unidadNueva: '',
            nuevaCualificacion: { codigo: '', texto: '' },
            nuevaUnidad: { codigo: '', texto: '' },
            formCualificacion: { codigo: '', texto: '' },
            formUnidad: { codigo: '', texto: '' },
            esEdicionC: false,
            esEdicionU: false
        };
    },

    mounted() {
        this.modalAsociar = new bootstrap.Modal(document.getElementById('modalAsociar'));
        this.modalCualificacion = new bootstrap.Modal(document.getElementById('modalCualificacion'));
        this.modalUnidad = new bootstrap.Modal(document.getElementById('modalUnidad'));
        this.cargar();
    },

    computed: {
        unidadesNoAsociadas() {
            return this.unidades.filter(u => !this.asociaciones.some(a => a.codigoUnidad === u.codigo));
        }
    },

    methods: {
        async cargar() {
            const r1 = await CualificacionesUCAPI.listar_cualificaciones();
            if (r1.success) this.cualificaciones = r1.data;
            const r2 = await CualificacionesUCAPI.listar_unidades();
            if (r2.success) this.unidades = r2.data;
        },

        async abrirAsociar(q) {
            this.cualificacionActual = q;
            this.unidadNueva = '';
            await this.cargarAsociaciones(q.codigo);
            this.modalAsociar.show();
        },

        async cargarAsociaciones(codigo) {
            const res = await CualificacionesUCAPI.listar_asociaciones(codigo);
            if (res.success) this.asociaciones = res.data;
        },

        async asociar() {
            if (!this.unidadNueva) return;
            const res = await CualificacionesUCAPI.guardar_asociacion({
                codigoCualificacion: this.cualificacionActual.codigo,
                codigoUnidad: this.unidadNueva
            });
            if (res.success) {
                this.unidadNueva = '';
                Swal.fire({ icon: 'success', title: 'Asociada', timer: 1000, showConfirmButton: false });
            }
        },

        async eliminarAsociacion(a) {
            const res = await CualificacionesUCAPI.eliminar_asociacion({
                codigoCualificacion: this.cualificacionActual.codigo,
                codigoUnidad: a.codigoUnidad
            });
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Desasociada', timer: 1000, showConfirmButton: false });
            }
        },

        abrirModal(q) {
            this.formCualificacion = { ...q };
            this.esEdicionC = true;
            this.modalCualificacion.show();
        },

        async guardarCualificacion() {
            const res = await CualificacionesUCAPI.guardar_cualificacion(this.nuevaCualificacion);
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Guardada', timer: 1000, showConfirmButton: false });
                this.nuevaCualificacion = { codigo: '', texto: '' };
                this.cargar();
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        },

        guardarCualificacionModal() {
            const res = CualificacionesUCAPI.guardar_cualificacion(this.formCualificacion);
            if (res.success) {
                this.modalCualificacion.hide();
                this.cargar();
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        },

        async eliminarCualificacion(q) {
            const conf = await Swal.fire({
                title: '¿Eliminar cualificación?',
                text: q.texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (conf.isConfirmed) {
                const res = await CualificacionesUCAPI.eliminar_cualificacion(q.codigo);
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1000, showConfirmButton: false });
                    this.cargar();
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            }
        },

        abrirUnidadModal(u) {
            this.formUnidad = { ...u };
            this.esEdicionU = true;
            this.modalUnidad.show();
        },

        guardarUnidad() {
            const res = CualificacionesUCAPI.guardar_unidad(this.nuevaUnidad);
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Guardada', timer: 1000, showConfirmButton: false });
                this.nuevaUnidad = { codigo: '', texto: '' };
                this.cargar();
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        },

        guardarUnidadModal() {
            const res = CualificacionesUCAPI.guardar_unidad(this.formUnidad);
            if (res.success) {
                this.modalUnidad.hide();
                this.cargar();
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        },

        async eliminarUnidad(u) {
            const conf = await Swal.fire({
                title: '¿Eliminar unidad?',
                text: u.texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (conf.isConfirmed) {
                const res = await CualificacionesUCAPI.eliminar_unidad(u.codigo);
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1000, showConfirmButton: false });
                    this.cargar();
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            }
        }
    }
};
