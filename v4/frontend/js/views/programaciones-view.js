const ProgramacionesView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-journal-bookmark me-2"></i>Programaciones Didácticas</h2>
                        <div>
                            <button class="btn btn-outline-primary me-2" @click="mostrarImportar">
                                <i class="bi bi-download me-1"></i>Importar
                            </button>
                            <button class="btn btn-primary" @click="nuevo">
                                <i class="bi bi-plus-lg me-1"></i>Nueva Programación
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Materia</label>
                    <select class="form-select" v-model="filtroMateria" @change="cargar">
                        <option value="">Todas</option>
                        <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                            {{ materia.titulo }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabla de programaciones -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Curso</th>
                                            <th>Materia</th>
                                            <th>Grupo</th>
                                            <th>Año</th>
                                            <th>Profesor</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="cargando">
                                            <td colspan="6" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else-if="programaciones.length === 0">
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                No hay programaciones registradas
                                            </td>
                                        </tr>
                                        <tr v-else v-for="prog in programaciones" :key="prog.id">
                                            <td>{{ prog.curso }}</td>
                                            <td>{{ prog.materia || 'Sin materia' }}</td>
                                            <td>{{ prog.grupo || 'Sin grupo' }}</td>
                                            <td>{{ prog.anyo || '-' }}</td>
                                            <td>{{ prog.profesor || '-' }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        @click="editar(prog)" 
                                                        title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        @click="eliminar(prog)" 
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Formulario -->
            <div class="modal fade" id="modalProgramacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalLabel">
                                {{ esNuevo ? 'Nueva Programación' : 'Editar Programación' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Curso *</label>
                                        <input type="text" class="form-control" v-model="formulario.curso" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Año</label>
                                        <input type="text" class="form-control" v-model="formulario.anyo">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Materia</label>
                                        <select class="form-select" v-model="formulario.idMateria">
                                            <option value="">Seleccionar materia</option>
                                            <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                                {{ materia.titulo }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Grupo</label>
                                        <select class="form-select" v-model="formulario.idGrupo">
                                            <option value="">Seleccionar grupo</option>
                                            <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                                {{ grupo.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Profesor</label>
                                    <input type="text" class="form-control" v-model="formulario.profesor">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Objetivos</label>
                                    <textarea class="form-control" v-model="formulario.objetivos" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Metodología</label>
                                    <textarea class="form-control" v-model="formulario.metodologia" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Evaluación</label>
                                    <textarea class="form-control" v-model="formulario.evaluacion" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Atención a la Diversidad</label>
                                    <textarea class="form-control" v-model="formulario.atencion_diversidad" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Materiales y Recursos</label>
                                    <textarea class="form-control" v-model="formulario.materiales" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Bibliografía</label>
                                    <textarea class="form-control" v-model="formulario.bibliografia" rows="3"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">
                                <i class="bi bi-save me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Importar Programación -->
            <div class="modal fade" id="modalImportar" tabindex="-1" aria-labelledby="modalImportarLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalImportarLabel">
                                <i class="bi bi-download me-2"></i>Importar Programación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="ejecutarImportar">
                                <div class="mb-3">
                                    <label class="form-label">Materia Origen *</label>
                                    <select class="form-select" v-model="importarForm.idMateriaOrigen" required>
                                        <option value="">--Selecciona una materia origen--</option>
                                        <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                            {{ materia.titulo }}
                                        </option>
                                    </select>
                                    <div class="form-text">Los datos de esta programación se copiarán a la materia destino.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Materia Destino *</label>
                                    <select class="form-select" v-model="importarForm.idMateriaDestino" required>
                                        <option value="">--Selecciona una materia destino--</option>
                                        <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                            {{ materia.titulo }}
                                        </option>
                                    </select>
                                    <div class="form-text">Esta materia recibirá los datos de la programación origen. ¡Se borrarán sus datos actuales!</div>
                                </div>
                                <div class="alert alert-warning" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Atención:</strong> Esta acción borrará todos los contenidos, temas y criterios de evaluación de la materia destino antes de importar los nuevos datos.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="ejecutarImportar">
                                <i class="bi bi-check-lg me-1"></i>Importar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            programaciones: [],
            materias: [],
            grupos: [],
            cargando: false,
            filtroMateria: '',
            formulario: {
                id: null,
                curso: '',
                anyo: '',
                idMateria: '',
                idGrupo: '',
                profesor: '',
                objetivos: '',
                metodologia: '',
                evaluacion: '',
                atencion_diversidad: '',
                materiales: '',
                bibliografia: ''
            },
            importarForm: {
                idMateriaOrigen: '',
                idMateriaDestino: ''
            },
            esNuevo: true,
            modal: null,
            modalImportar: null
        };
    },

    async mounted() {
        await this.cargar();
        await this.cargarCatalogos();
        this.modal = new bootstrap.Modal(document.getElementById('modalProgramacion'));
        this.modalImportar = new bootstrap.Modal(document.getElementById('modalImportar'));
    },

    methods: {
        async cargar() {
            this.cargando = true;
            try {
                this.programaciones = await programacionesAPI.listar(this.filtroMateria || null);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        async cargarCatalogos() {
            try {
                const [materiasRes, gruposRes] = await Promise.all([
                    fetch('backend/api/materias/index.php?action=listar').then(r => r.json()),
                    fetch('backend/api/grupos/index.php?action=listar').then(r => r.json())
                ]);
                if (materiasRes.success) this.materias = materiasRes.data || [];
                if (gruposRes.success) this.grupos = gruposRes.data || [];
            } catch (error) {
                console.error('Error al cargar catálogos:', error);
            }
        },

        nuevo() {
            this.esNuevo = true;
            this.formulario = {
                id: null,
                curso: '',
                anyo: '',
                idMateria: '',
                idGrupo: '',
                profesor: '',
                objetivos: '',
                metodologia: '',
                evaluacion: '',
                atencion_diversidad: '',
                materiales: '',
                bibliografia: ''
            };
            this.modal.show();
        },

        editar(prog) {
            this.esNuevo = false;
            this.formulario = { ...prog };
            this.modal.show();
        },

        async guardar() {
            try {
                await programacionesAPI.guardar(this.formulario);
                Swal.fire('Éxito', 'Programación guardada correctamente', 'success');
                this.modal.hide();
                await this.cargar();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async eliminar(prog) {
            const result = await Swal.fire({
                title: '¿Eliminar programación?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await programacionesAPI.eliminar(prog.id);
                    Swal.fire('Eliminada', 'Programación eliminada correctamente', 'success');
                    await this.cargar();
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            }
        },

        mostrarImportar() {
            this.importarForm = {
                idMateriaOrigen: '',
                idMateriaDestino: ''
            };
            this.modalImportar.show();
        },

        async ejecutarImportar() {
            if (!this.importarForm.idMateriaOrigen || !this.importarForm.idMateriaDestino) {
                Swal.fire('Error', 'Debe seleccionar ambas materias', 'error');
                return;
            }

            if (this.importarForm.idMateriaOrigen === this.importarForm.idMateriaDestino) {
                Swal.fire('Error', 'Las materias origen y destino deben ser diferentes', 'error');
                return;
            }

            const result = await Swal.fire({
                title: '¿Confirmar importación?',
                html: '<p>Se borrarán todos los datos de la materia destino y se copiarán los de la materia origen.</p><p class="text-danger"><strong>¡Esta acción no se puede deshacer!</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, importar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await programacionesAPI.importar(
                        this.importarForm.idMateriaOrigen,
                        this.importarForm.idMateriaDestino
                    );
                    Swal.fire('Éxito', 'Programación importada correctamente', 'success');
                    this.modalImportar.hide();
                    await this.cargar();
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            }
        }
    }
};
