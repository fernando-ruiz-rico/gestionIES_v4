// Vista de Ciclos Formativos
// Permite gestionar los ciclos, sus cursos asociados y sus unidades de
// competencia (mismo contenido que la página de v3).

const CiclosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-layers"></i> Ciclos Formativos</h2>
                    <button class="btn btn-primary" @click="abrirModalCrear()">
                        <i class="bi bi-plus-lg"></i> Nuevo Ciclo
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Familia</th>
                                        <th>Nivel</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in ciclos" :key="c.id">
                                        <td>{{ c.id }}</td>
                                        <td>{{ c.nombre }}</td>
                                        <td>{{ c.familia }}</td>
                                        <td>{{ c.nivel }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1" @click="verAsociacionesCursos(c)">
                                                <i class="bi bi-tree"></i> Cursos
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary me-1" @click="verAsociacionesUnidades(c)">
                                                <i class="bi bi-diagram-3"></i> UCs
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(c)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!ciclos.length">
                                        <td colspan="5" class="text-center text-muted py-4">Sin ciclos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal alta/edición de ciclo -->
            <div class="modal fade" id="modalCiclo" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} Ciclo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" v-model="form.nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Familia *</label>
                                    <input type="text" class="form-control" v-model="form.familia" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nivel *</label>
                                    <select class="form-select" v-model="form.nivel" required>
                                        <option value="">--Selecciona un nivel--</option>
                                        <option v-for="n in niveles" :key="n" :value="n">{{ n }}</option>
                                    </select>
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

            <!-- Modal de cursos asociados al ciclo -->
            <div class="modal fade" id="modalAsocCursos" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cursos asociados: {{ cicloActual.nombre }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm" v-if="cursosAsociados.asociados.length">
                                <thead>
                                    <tr><th>Orden</th><th>Curso</th><th class="text-end">Acciones</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="a in cursosAsociados.asociados" :key="a.idCurso">
                                        <td>
                                            <input type="number" min="1" class="form-control form-control-sm" style="width:5rem"
                                                   v-model.number="a.orden" @change="actualizarOrdenCurso(a)">
                                        </td>
                                        <td>{{ a.nombre }} <small class="text-muted">({{ a.abreviatura }})</small></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" @click="borrarCurso(a)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-muted">Este ciclo no tiene cursos asociados.</p>

                            <p class="mb-1">Asociar un nuevo curso</p>
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <select class="form-select" v-model="cursoNuevo">
                                        <option :value="0">--Selecciona un curso--</option>
                                        <option v-for="d in cursosAsociados.disponibles" :key="d.id" :value="d.id">
                                            {{ d.nombre }} ({{ d.abreviatura }})
                                        </option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <span class="input-group-text">Orden</span>
                                        <input type="number" min="1" class="form-control" style="width:5rem" v-model.number="ordenNuevoCurso">
                                        <button class="btn btn-primary" :disabled="!cursoNuevo" @click="anadirCurso">Añadir</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de unidades de competencia asociadas al ciclo -->
            <div class="modal fade" id="modalAsocUnidades" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Unidades de competencia: {{ cicloActual.nombre }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="unidadesAsociadas.asociadas.length">
                                <p v-for="u in unidadesAsociadas.asociadas" :key="u.codigo" class="d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-danger me-2" @click="borrarUnidad(u)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <span>{{ u.codigo }} - {{ u.texto }}</span>
                                </p>
                            </div>
                            <p v-else class="text-muted">Este ciclo no tiene unidades asociadas.</p>

                            <p class="mb-1">Asociar una nueva unidad</p>
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <select class="form-select" v-model="unidadNueva">
                                        <option value="">--Selecciona una unidad--</option>
                                        <option v-for="d in unidadesAsociadas.disponibles" :key="d.codigo" :value="d.codigo">
                                            {{ d.codigo }} - {{ d.texto }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" :disabled="!unidadNueva" @click="anadirUnidad">Añadir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            ciclos: [],
            form: { id: 0, nombre: '', familia: '', nivel: '' },
            niveles: [
                'Ciclo Formativo de Grado Básico',
                'Ciclo Formativo de Grado Medio',
                'Ciclo Formativo de Grado Superior',
                'Curso de Especialización'
            ],
            esEdicion: false,
            // Ciclo del que se están viendo las asociaciones
            cicloActual: { id: 0, nombre: '' },
            cursosAsociados: { asociados: [], disponibles: [] },
            cursoNuevo: 0,
            ordenNuevoCurso: 1,
            unidadesAsociadas: { asociadas: [], disponibles: [] },
            unidadNueva: '',
            modal: null,
            modalCursos: null,
            modalUnidades: null
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalCiclo'));
        this.modalCursos = new bootstrap.Modal(document.getElementById('modalAsocCursos'));
        this.modalUnidades = new bootstrap.Modal(document.getElementById('modalAsocUnidades'));
    },

    methods: {
        async cargar() {
            const result = await CiclosAPI.listar();
            if (result.success) {
                this.ciclos = result.data;
            } else {
                this.ciclos = [];
            }
        },

        abrirModalCrear() {
            this.form = { id: 0, nombre: '', familia: '', nivel: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(ciclo) {
            this.form = { id: ciclo.id, nombre: ciclo.nombre, familia: ciclo.familia, nivel: ciclo.nivel };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await CiclosAPI.guardar(this.form);

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                this.modal.hide();
                this.cargar();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
        },

        eliminar(ciclo) {
            Swal.fire({
                title: '¿Eliminar ciclo?',
                text: ciclo.nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await CiclosAPI.eliminar(ciclo.id);

                    if (result.success) {
                        Swal.fire({ icon: 'success', timer: 1500, showConfirmButton: false });
                        this.cargar();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: result.error });
                    }
                }
            });
        },

        // Cursos asociados al ciclo

        async verAsociacionesCursos(ciclo) {
            this.cicloActual = { id: ciclo.id, nombre: ciclo.nombre };
            this.cursoNuevo = 0;
            this.ordenNuevoCurso = 1;
            const result = await CiclosAPI.asociacionesCursos(ciclo.id);
            if (result.success) {
                this.cursosAsociados = result.data;
                // El orden por defecto para nuevos cursos es el siguiente al mayor existente
                const ordenes = this.cursosAsociados.asociados.map(a => a.orden);
                this.ordenNuevoCurso = ordenes.length ? Math.max(...ordenes) + 1 : 1;
                this.modalCursos.show();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
        },

        async anadirCurso() {
            const result = await CiclosAPI.guardarAsociacionCurso({
                idCiclo: this.cicloActual.id,
                idCurso: this.cursoNuevo,
                orden: this.ordenNuevoCurso
            });
            if (!result.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
                return;
            }
            this.cursoNuevo = 0;
            this.cargarAsociacionesCursos();
        },

        async actualizarOrdenCurso(asociacion) {
            const result = await CiclosAPI.guardarAsociacionCurso({
                idCiclo: this.cicloActual.id,
                idCurso: asociacion.idCurso,
                orden: asociacion.orden
            });
            if (!result.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
        },

        async borrarCurso(asociacion) {
            const result = await CiclosAPI.borrarAsociacionCurso(this.cicloActual.id, asociacion.idCurso);
            if (!result.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
            this.cargarAsociacionesCursos();
        },

        async cargarAsociacionesCursos() {
            const result = await CiclosAPI.asociacionesCursos(this.cicloActual.id);
            if (result.success) {
                this.cursosAsociados = result.data;
            }
        },

        // Unidades de competencia asociadas al ciclo

        async verAsociacionesUnidades(ciclo) {
            this.cicloActual = { id: ciclo.id, nombre: ciclo.nombre };
            this.unidadNueva = '';
            const result = await CiclosAPI.asociacionesUnidades(ciclo.id);
            if (result.success) {
                this.unidadesAsociadas = result.data;
                this.modalUnidades.show();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
        },

        async anadirUnidad() {
            const result = await CiclosAPI.guardarAsociacionUnidad(this.cicloActual.id, this.unidadNueva);
            if (!result.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
                return;
            }
            this.unidadNueva = '';
            this.cargarAsociacionesUnidades();
        },

        async borrarUnidad(unidad) {
            const result = await CiclosAPI.borrarAsociacionUnidad(this.cicloActual.id, unidad.codigo);
            if (!result.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
            this.cargarAsociacionesUnidades();
        },

        async cargarAsociacionesUnidades() {
            const result = await CiclosAPI.asociacionesUnidades(this.cicloActual.id);
            if (result.success) {
                this.unidadesAsociadas = result.data;
            }
        }
    }
};
