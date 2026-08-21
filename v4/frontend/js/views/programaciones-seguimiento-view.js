const ProgramacionesSeguimientoView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-graph-up me-2"></i>Seguimiento de Programaciones</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Registra el seguimiento de la programación de cada grupo en cada evaluación.
                        Curso actual: <strong>{{ cursoActual }}</strong>.
                    </div>
                </div>
            </div>

            <!-- Selector de profesor (solo admin) -->
            <div class="row mb-3" v-if="esAdmin">
                <div class="col-md-6">
                    <label for="selectorProfesor" class="form-label">Profesor</label>
                    <select id="selectorProfesor" class="form-select" v-model="idProfesor" @change="cambiarProfesor">
                        <option :value="0">--Selecciona un profesor--</option>
                        <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Selectores de evaluación, materia y grupo (mismo orden que v3) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="selectorEvaluacion" class="form-label">Evaluación</label>
                    <select id="selectorEvaluacion" class="form-select" v-model="idEvaluacion" @change="cambiarEvaluacion">
                        <option :value="0">--Selecciona evaluación--</option>
                        <option v-for="e in evaluaciones" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="selectorMateria" class="form-label">Materia</label>
                    <select id="selectorMateria" class="form-select" v-model="idMateria" @change="cambiarMateria">
                        <option :value="0">--Selecciona una materia--</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }} ({{ m.nomCurso }})</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="selectorGrupo" class="form-label">Grupo</label>
                    <select id="selectorGrupo" class="form-select" v-model="idGrupo" @change="cambiarGrupo">
                        <option :value="0">--Selecciona un grupo--</option>
                        <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- PDF buttons (pendientes de Fase 8) -->
            <div class="row mb-3">
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <button class="btn btn-light" :disabled="!idEvaluacion" @click="generarPDF('Ciclos Formativos')">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF seguimiento Ciclos Formativos
                    </button>
                </div>

                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <button class="btn btn-light" :disabled="!idEvaluacion" @click="generarPDF('ESO/BACH')">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF seguimiento ESO/BACH
                    </button>
                </div>
            </div>

            <!-- Editor: se muestra cuando hay selección completa (evaluación + materia + grupo) -->
            <div class="row" v-if="completa">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                Seguimiento — {{ materiaSeleccionada }} / {{ grupoSeleccionada }} — {{ evaluacionSeleccionada }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mt-4">
                                <label for="editorTemporalizacion" class="form-label mb-2">
                                    SEGUIMIENTO DE LA PROGRAMACIÓN, con respecto a la temporalización que figura en las Propuestas Pedagógicas:
                                </label>
                                <textarea id="editorTemporalizacion"></textarea>
                            </div>

                            <div class="mt-4">
                                <label for="editorResultados" class="form-label mb-2">
                                    VALORACIÓN DE LOS RESULTADOS ACADÉMICOS, detallando cumplimiento de programación, incidencia sobre la convivencia del grupo y resultados académicos:
                                </label>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Aprobados:</span>
                                            <input type="number" class="form-control" v-model="numAprobados" min="0" max="99">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Suspensos:</span>
                                            <input type="number" class="form-control" v-model="numSuspensos" min="0" max="99">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Otros:</span>
                                            <input type="number" class="form-control" v-model="numOtros" min="0" max="99">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Total:</span>
                                            <input type="number" class="form-control" :value="totalAlumnos" disabled readonly>
                                        </div>
                                    </div>
                                </div>
                                <textarea id="editorResultados"></textarea>
                            </div>

                            <div class="mt-4">
                                <label for="editorInclusion" class="form-label mb-2">
                                    INCLUSIÓN DEL ALUMNADO (si procede), detallando la valoración de los resultados de alumnado a quien se le ha aplicado algún tipo de respuesta educativa:
                                </label>
                                <textarea id="editorInclusion"></textarea>
                            </div>

                            <div class="text-center mt-4 mb-2">
                                <button class="btn btn-primary me-2" @click="guardar" :disabled="guardando">
                                    <i class="bi bi-save me-1"></i>
                                    {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                                </button>
                                <button class="btn btn-outline-secondary" @click="vistaPrevia">
                                    <i class="bi bi-eye me-1"></i>Vista previa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso si no hay selección completa -->
            <div class="row mt-4" v-if="!completa">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Selecciona evaluación, materia y grupo para comenzar.
                    </div>
                </div>
            </div>

            <!-- Modal de vista previa -->
            <div class="modal fade" id="modalVistaPrevia" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Vista previa — Seguimiento</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Temporalización</h6>
                            <div v-html="temporalizacion || '—'"></div>

                            <hr>

                            <h6>Resultados académicos</h6>
                            <ul class="list-unstyled">
                                <li>Aprobados: {{ numAprobados }}</li>
                                <li>Suspensos: {{ numSuspensos }}</li>
                                <li>Otros: {{ numOtros }}</li>
                                <li>Total: {{ totalAlumnos }}</li>
                            </ul>
                            <div v-html="resultados || '—'"></div>

                            <hr>

                            <h6>Inclusión del alumnado</h6>
                            <div v-html="inclusion || '—'"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            profesores: [],
            idProfesor: 0,
            evaluaciones: [],
            idEvaluacion: 0,
            materias: [],
            grupos: [],
            idMateria: 0,
            idGrupo: 0,
            temporalizacion: '',
            resultados: '',
            inclusion: '',
            numAprobados: 0,
            numSuspensos: 0,
            numOtros: 0,
            guardando: false,
            modalVistaPrevia: null
        };
    },

    computed: {
        esAdmin() {
            return this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento';
        },

        completa() {
            return this.idMateria > 0 && this.idGrupo > 0 && this.idEvaluacion > 0;
        },

        totalAlumnos() {
            return (this.numAprobados || 0) + (this.numSuspensos || 0) + (this.numOtros || 0);
        },

        // Mismo criterio que cursoActual() de v3:
        // de septiembre a agosto del año siguiente -> X/(X+1)
        cursoActual() {
            const ahora = new Date();
            const mes = ahora.getMonth() + 1;
            const anyo = ahora.getFullYear();
            if (mes >= 9) {
                return anyo + '/' + (anyo + 1);
            }
            return (anyo - 1) + '/' + anyo;
        },

        materiaSeleccionada() {
            const m = this.materias.find(x => x.id === this.idMateria);
            return m ? m.nombre : '';
        },

        grupoSeleccionada() {
            const g = this.grupos.find(x => x.id === this.idGrupo);
            return g ? g.nombre : '';
        },

        evaluacionSeleccionada() {
            const e = this.evaluaciones.find(x => x.id === this.idEvaluacion);
            return e ? e.nombre : '';
        }
    },

    async mounted() {
        this.modalVistaPrevia = new bootstrap.Modal(document.getElementById('modalVistaPrevia'));

        if (this.esAdmin) {
            await this.cargarProfesores();
        } else {
            // Un profesor usa siempre su propio id; no necesita desplegable
            this.idProfesor = 0; // El backend usa la sesión
        }

        await this.cargarEvaluaciones();
        await this.cargarMaterias();
    },

    beforeUnmount() {
        this.borrarEditores();
    },

    methods: {
        // --- TinyMCE (misma configuración que 2.3/2.4) ---
        idsEditores() {
            return ['editorTemporalizacion', 'editorResultados', 'editorInclusion'];
        },

        inicializarEditores() {
            if (!window.tinymce) {
                console.warn('TinyMCE no disponible — se muestran los textareas planos');
                return;
            }

            // Cargar el contenido en los textareas antes de inicializar el editor
            const areaTemporalizacion = document.getElementById('editorTemporalizacion');
            const areaResultados = document.getElementById('editorResultados');
            const areaInclusion = document.getElementById('editorInclusion');
            if (!areaTemporalizacion || !areaResultados || !areaInclusion) return;
            areaTemporalizacion.value = this.temporalizacion || '';
            areaResultados.value = this.resultados || '';
            areaInclusion.value = this.inclusion || '';

            this.borrarEditores();

            tinymce.init({
                selector: 'textarea#editorTemporalizacion, textarea#editorResultados, textarea#editorInclusion',
                height: 300,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css',
                setup: (editor) => {
                    const mapa = {
                        editorTemporalizacion: 'temporalizacion',
                        editorResultados: 'resultados',
                        editorInclusion: 'inclusion'
                    };
                    const campo = mapa[editor.id];
                    editor.on('change', () => {
                        this[campo] = editor.getContent();
                    });
                }
            });
        },

        borrarEditores() {
            this.idsEditores().forEach(id => {
                if (window.tinymce && tinymce.get(id)) {
                    tinymce.remove(id);
                }
            });
        },

        // Sincroniza el estado con los editores y devuelve el contenido final
        leerEditores() {
            const mapa = {
                editorTemporalizacion: 'temporalizacion',
                editorResultados: 'resultados',
                editorInclusion: 'inclusion'
            };
            this.idsEditores().forEach(id => {
                const editor = window.tinymce ? tinymce.get(id) : null;
                if (editor) {
                    editor.save();
                    this[mapa[id]] = editor.getContent();
                }
            });
        },

        // --- Carga de datos ---
        async cargarProfesores() {
            try {
                this.profesores = await programacionesSeguimientoAPI.cargarProfesores() || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarProfesor() {
            // Igual que en v3: al cambiar de profesor se recarga todo el formulario
            this.idMateria = 0;
            this.idGrupo = 0;
            this.idEvaluacion = 0;
            this.borrarEditores();
            await this.cargarMaterias();
        },

        async cargarEvaluaciones() {
            try {
                this.evaluaciones = await programacionesSeguimientoAPI.cargarEvaluaciones() || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cargarMaterias() {
            if (this.esAdmin && this.idProfesor <= 0) return;
            try {
                const data = await programacionesSeguimientoAPI.cargarMaterias(this.idProfesor);
                this.materias = data || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarMateria() {
            this.idGrupo = 0;
            this.borrarEditores();
            if (this.idMateria <= 0) return;

            try {
                const data = await programacionesSeguimientoAPI.cargarGrupos(this.idMateria, this.idProfesor);
                this.grupos = data || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }

            await this.alCambiarSelector();
        },

        async cambiarGrupo() {
            await this.alCambiarSelector();
        },

        async cambiarEvaluacion() {
            await this.alCambiarSelector();
        },

        // Recarga el seguimiento cuando cambia algún selector
        async alCambiarSelector() {
            this.borrarEditores();

            // Reinicia los campos en vacío
            this.temporalizacion = '';
            this.resultados = '';
            this.inclusion = '';
            this.numAprobados = 0;
            this.numSuspensos = 0;
            this.numOtros = 0;

            if (!this.completa) return;

            try {
                const data = await programacionesSeguimientoAPI.cargar(this.idMateria, this.idGrupo, this.idEvaluacion, this.idProfesor);
                this.temporalizacion = data.temporalizacion || '';
                this.resultados = data.resultados || '';
                this.inclusion = data.inclusion || '';
                this.numAprobados = data.num_aprobados || 0;
                this.numSuspensos = data.num_suspensos || 0;
                this.numOtros = data.num_otros || 0;

                this.$nextTick(() => {
                    this.inicializarEditores();
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Guardar ---
        async guardar() {
            this.leerEditores();

            this.guardando = true;
            try {
                await programacionesSeguimientoAPI.guardar({
                    idMateria: this.idMateria,
                    idGrupo: this.idGrupo,
                    idEvaluacion: this.idEvaluacion,
                    idProfesor: this.idProfesor,
                    temporalizacion: this.temporalizacion,
                    resultados: this.resultados,
                    inclusion: this.inclusion,
                    num_aprobados: this.numAprobados,
                    num_suspensos: this.numSuspensos,
                    num_otros: this.numOtros
                });
                Swal.fire('Éxito', 'Seguimiento guardado correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        // --- Vista previa ---
        vistaPrevia() {
            this.leerEditores();
            this.modalVistaPrevia.show();
        },

        // --- PDFs (pendientes Fase 8) ---
        generarPDF(categoria) {
            if (!this.idEvaluacion) return;
            Swal.fire({
                title: 'Pendiente',
                text: `La generación del PDF de seguimiento (${categoria}) está pendiente (Fase 8)`,
                icon: 'info'
            });
        }
    }
};
