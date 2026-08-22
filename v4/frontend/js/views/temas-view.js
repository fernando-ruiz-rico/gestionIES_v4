// Fase 2.6 — Gestión de temas / unidades de una materia
// Equivalente a v3: temas.php (listado + totales) y editar_tema.php (formulario completo).
const TemasView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-stack me-2"></i>Unidades de programación</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Gestiona los temas / unidades de programación de cada materia.
                        Cada tema rellena sus apartados, los resultados de aprendizaje (RA) y
                        criterios de evaluación (CE) que lo componen, así como sus competencias.
                    </div>
                </div>
            </div>

            <!-- Selector de materia -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="selectorMateria" class="form-label">Materia</label>
                    <select id="selectorMateria" class="form-select" v-model="idMateria" @change="cambiarMateria">
                        <option :value="0">--Selecciona una materia--</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.materia }} ({{ m.curso }})</option>
                    </select>
                </div>
            </div>

            <!-- Listado de temas + totales -->
            <div class="row mt-4" v-if="idMateria > 0">
                <div class="col-12">
                    <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px">#</th>
                                <th>Título</th>
                                <th style="width: 180px">Evaluación</th>
                                <th style="width: 120px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in temas" :key="t.id">
                                <td class="text-center">{{ t.orden }}.</td>
                                <td>{{ t.titulo }}</td>
                                <td><span class="me-2">{{ t.peso_evaluacion }}% ({{ t.horas }}h)</span></td>
                                <td class="text-end">
                                    <button class="btn btn-outline-danger btn-sm" @click="borrarTema(t)" title="Borrar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <a class="btn btn-outline-secondary btn-sm" @click="editarTema(t.id)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>

                    <!-- Totales (igual criterio que v3) -->
                    <div class="text-center fw-bold" v-if="sumaPesos > 0 || sumaHoras > 0">
                        Total:
                        <span :class="errorPesos ? 'text-danger' : 'text-success'">{{ sumaPesos }}%</span>
                        (
                        <span :class="errorHoras ? 'text-danger' : 'text-success'">{{ sumaHoras }} / {{ horasAnuales }} horas</span>
                        )
                        <template v-if="errorPesos || errorHoras">
                            <div class="fst-italic fs-6 text-danger">
                                <template v-if="errorPesos">El porcentaje debe ser 100%. </template>
                                <template v-if="errorHoras">El total de horas debe coincidir con las horas anuales de la materia.</template>
                            </div>
                        </template>
                        <i v-else class="bi bi-emoji-smile ms-2"></i>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary" @click="nuevoTema()">
                            <i class="bi bi-plus-lg me-1"></i>Nueva Unidad
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mt-4" v-if="!idMateria">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Selecciona una materia para ver sus unidades de programación.
                    </div>
                </div>
            </div>

            <!-- ============ EDITOR DE TEMA (se muestra al seleccionar un tema) ============ -->
            <div class="row mt-4" v-if="idTema > 0 && tema">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Formulario de edición de tema / unidad</h5>
                            <button class="btn btn-light btn-sm" @click="cerrarEdicion">
                                <i class="bi bi-x-lg me-1"></i>Cerrar edición
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Campos básicos -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-2">
                                    <label for="orden" class="form-label">Número del tema</label>
                                    <input id="orden" type="number" class="form-control" v-model.number="tema.orden" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="horas" class="form-label">Horas de la unidad</label>
                                    <input id="horas" type="number" class="form-control" v-model.number="tema.horas" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label for="horasAnuales" class="form-label">Horas anuales</label>
                                    <input id="horasAnuales" type="number" class="form-control" :value="horasAnuales" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label for="trimestre" class="form-label">Trimestre</label>
                                    <select id="trimestre" class="form-select" v-model.number="tema.trimestre">
                                        <option :value="0">-- Selecciona un trimestre --</option>
                                        <option :value="1">1º Trimestre</option>
                                        <option :value="2">2º Trimestre</option>
                                        <option :value="3">3º Trimestre</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="peso" class="form-label">% Peso evaluación anual</label>
                                    <input id="peso" type="number" class="form-control" v-model.number="tema.peso_evaluacion" min="0" max="100">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-md-2">
                                    <label for="titulo" class="form-label">Título</label>
                                </div>
                                <div class="col-md-10">
                                    <input id="titulo" type="text" class="form-control" v-model="tema.titulo">
                                </div>
                            </div>

                            <!-- Pestañas -->
                            <ul class="nav nav-tabs mt-2" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#seccion_descripcion" type="button">Descripción</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_justificacion" type="button">Justificación</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_contexto" type="button">Contexto</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_contenidos" type="button">{{ idCiclo > 0 ? 'Contenidos' : 'Saberes básicos' }}</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_secuenciacion" type="button">Secuenciación/Actividades</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_recursos" type="button">Recursos</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_evaluacion" type="button">Evaluación</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_metodologia" type="button">Metodología</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_adaptaciones" type="button">Adaptaciones</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_ra_ce" type="button">{{ idCiclo > 0 ? 'RA/CE' : 'CE/CR' }}</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#seccion_competencias" type="button">{{ idCiclo > 0 ? 'Competencias' : 'Competencias clave' }}</button></li>
                            </ul>

                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="seccion_descripcion" role="tabpanel">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="datostema" id="descripcion"></textarea>
                                </div>
                                <div class="tab-pane fade" id="seccion_justificacion" role="tabpanel">
                                    <label for="justificacion" class="form-label">Justificación</label>
                                    <textarea class="datostema" id="justificacion"></textarea>
                                </div>
                                <div class="tab-pane fade" id="seccion_contexto" role="tabpanel">
                                    <label for="contexto" class="form-label">Contexto</label>
                                    <textarea class="datostema" id="contexto"></textarea>
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" id="contexto_defecto" v-model="tema.contextoDefecto">
                                        <label class="form-check-label" for="contexto_defecto">Dejar valores por defecto para este campo</label>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="seccion_contenidos" role="tabpanel">
                                    <label for="contenidos" class="form-label">{{ idCiclo > 0 ? 'Contenidos' : 'Saberes básicos' }}</label>
                                    <textarea class="datostema" id="contenidos"></textarea>
                                </div>
                                <div class="tab-pane fade" id="seccion_secuenciacion" role="tabpanel">
                                    <label for="secuenciacion" class="form-label">Secuenciación</label>
                                    <textarea class="datostema" id="secuenciacion"></textarea>
                                </div>
                                <div class="tab-pane fade" id="seccion_recursos" role="tabpanel">
                                    <label for="recursos" class="form-label">Recursos</label>
                                    <textarea class="datostema" id="recursos"></textarea>
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" id="recursos_defecto" v-model="tema.recursosDefecto">
                                        <label class="form-check-label" for="recursos_defecto">Dejar valores por defecto para este campo</label>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="seccion_evaluacion" role="tabpanel">
                                    <label for="evaluacion" class="form-label">Evaluación</label>
                                    <textarea class="datostema" id="evaluacion"></textarea>
                                    <button class="btn btn-primary mt-2" type="button" @click="repetirEvaluacion">
                                        <i class="bi bi-arrow-left-right me-1"></i>Repetir en resto de unidades
                                    </button>
                                </div>
                                <div class="tab-pane fade" id="seccion_metodologia" role="tabpanel">
                                    <label for="metodologia" class="form-label">Metodología</label>
                                    <textarea class="datostema" id="metodologia"></textarea>
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" id="metodologia_defecto" v-model="tema.metodologiaDefecto">
                                        <label class="form-check-label" for="metodologia_defecto">Dejar valores por defecto para este campo</label>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="seccion_adaptaciones" role="tabpanel">
                                    <label for="adaptaciones" class="form-label">Adaptaciones</label>
                                    <textarea class="datostema" id="adaptaciones"></textarea>
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" id="adaptaciones_defecto" v-model="tema.adaptacionesDefecto">
                                        <label class="form-check-label" for="adaptaciones_defecto">Dejar valores por defecto para este campo</label>
                                    </div>
                                </div>

                                <!-- Pestaña RA/CE (acordeón dinámico) -->
                                <div class="tab-pane fade" id="seccion_ra_ce" role="tabpanel">
                                    <p class="mb-2 d-flex justify-content-between align-items-center">
                                        <span>{{ idCiclo > 0 ? 'Resultados de aprendizaje y criterios de evaluación' : 'Competencias específicas y criterios de evaluación' }}</span>
                                        <button type="button" class="btn btn-sm btn-secondary" @click="calcularPorcentajes">Calcular y actualizar porcentajes</button>
                                    </p>
                                    <div v-if="ra.length === 0" class="text-muted">
                                        No hay {{ idCiclo > 0 ? 'resultados de aprendizaje' : 'competencias específicas' }} definidos para esta materia.
                                    </div>
                                    <div v-else class="accordion" id="accordionRA">
                                        <div class="accordion-item mb-0" v-for="r in ra" :key="r.id">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapseRA' + r.orden" aria-expanded="false">
                                                    <input type="checkbox" class="form-check-input mt-0 me-2 check_ra" :id="'ra' + r.id" :checked="raTodosMarcados(r.id)" @change="marcarDesmarcar(r.id)">
                                                    <span class="d-inline-block text-truncate" style="width: 95%" :title="r.texto">{{ r.orden }}. {{ r.texto }}</span>
                                                    <button class="btn btn-sm btn-secondary px-2 py-0 mx-2" style="min-width: 5%" title="Pulsa para cambiar" @click.stop="cargarModalRA(r.id)">{{ r.porcentaje_evaluacion }}%</button>
                                                </button>
                                            </h2>
                                            <div :id="'collapseRA' + r.orden" class="accordion-collapse collapse">
                                                <div class="accordion-body">
                                                    <template v-if="r.ce.length === 0">
                                                        <em class="text-muted">No hay criterios de evaluación definidos.</em>
                                                    </template>
                                                    <div v-for="ce in r.ce" :key="ce.codigo" class="form-check mb-0">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input me-2 check_ce" v-model="selCE[ceKey(r.id, ce.codigo)]">
                                                            {{ r.orden }}.{{ ce.codigo }}. {{ ce.texto }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item text-center p-1 mb-0">
                                            <span :class="totalRA === 100 ? 'text-success' : 'text-danger'">Suma: {{ totalRA }}%</span> (evaluación anual)
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestaña Competencias (checkboxes) -->
                                <div class="tab-pane fade" id="seccion_competencias" role="tabpanel">
                                    <p>
                                        {{ idCiclo > 0 ? 'Competencias profesionales (negro) y para la empleabilidad (verde)' : 'Competencias clave' }}
                                    </p>
                                    <template v-if="competencias.length === 0">
                                        <p class="text-muted">No hay competencias de tipo 1 en la materia ni de tipo 2 en el ciclo.</p>
                                    </template>
                                    <div v-for="c in competencias" :key="c.id" class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input me-1 check_com" :id="'com' + c.id" v-model="selCom[c.id]">
                                        <label :class="c.tipo === 1 ? '' : 'text-success'" :for="'com' + c.id" :title="c.texto">{{ c.codigo }}. {{ c.texto }}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones finales -->
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                                    <i class="bi bi-save me-1"></i>
                                    {{ guardando ? 'Guardando...' : 'Guardar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: nueva unidad -->
            <div class="modal fade" id="formnuevotema" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Formulario de nuevo tema / unidad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label" for="ordenNuevo">Número de tema</label>
                                <input type="number" class="form-control" id="ordenNuevo" v-model.number="nuevaForm.orden" min="1">
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="tituloNuevo">Título</label>
                                <input type="text" class="form-control" id="tituloNuevo" v-model="nuevaForm.titulo">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" @click="guardarNuevo">Enviar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: edición de un RA -->
            <div class="modal fade" id="formresultado_ra" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Evaluación del {{ idCiclo > 0 ? 'Resultado de Aprendizaje (RA)' : 'Competencia Específica (CE)' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model.number="raEdit.id">
                            <h6 class="mb-4">{{ idCiclo > 0 ? 'RA' : 'CE' }}{{ raEdit.orden }}. {{ raEdit.texto }}</h6>
                            <div class="mb-3">
                                <label class="form-label" for="porcentajeEvaluacion">Porcentaje en la evaluación global</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="porcentajeEvaluacion" v-model.number="raEdit.porcentaje" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="esClave" v-model="raEdit.esClave">
                                <label class="form-check-label" for="esClave">{{ idCiclo > 0 ? 'RA' : 'CE' }} clave</label>
                                <div class="form-text">
                                    {{ raEdit.esClave ? '(Se debe superar para aprobar la materia)' : '(Se puede no superar y aprobar la materia)' }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" @click="guardarRA">Enviar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
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
            materias: [],
            idMateria: 0,
            horasAnuales: 0,

            temas: [],
            idTema: 0,
            tema: null,

            // Estado de selección de CE/competencias (mapa reactiva)
            selCE: {},
            selCom: {},

            // Datos a nivel de materia: RA/CE + competencias
            idCiclo: 0,
            ra: [],
            totalRA: 0,
            competencias: [],

            nuevaForm: { orden: 0, titulo: '' },
            raEdit: { id: 0, orden: 0, texto: '', porcentaje: 0, esClave: false },

            guardando: false,
            modalNueva: null,
            modalRA: null
        };
    },

    computed: {
        sumaPesos() {
            return this.temas.reduce((a, t) => a + (parseInt(t.peso_evaluacion) || 0), 0);
        },
        sumaHoras() {
            return this.temas.reduce((a, t) => a + (parseInt(t.horas) || 0), 0);
        },
        errorPesos() {
            return this.sumaPesos !== 100;
        },
        errorHoras() {
            return this.sumaHoras !== this.horasAnuales;
        }
    },

    async mounted() {
        this.modalNueva = new bootstrap.Modal(document.getElementById('formnuevotema'));
        this.modalRA = new bootstrap.Modal(document.getElementById('formresultado_ra'));
        await this.cargarMaterias();
    },

    beforeUnmount() {
        this.borrarEditores();
    },

    methods: {
        // --- TinyMCE (misma configuración que v3: initTinyMCE('datostema', 350)) ---
        camposEditores() {
            return ['descripcion', 'justificacion', 'contexto', 'contenidos', 'secuenciacion', 'recursos', 'evaluacion', 'metodologia', 'adaptaciones'];
        },

        inicializarEditores() {
            if (!window.tinymce) {
                console.warn('TinyMCE no disponible — se muestran los textareas planos');
                return;
            }
            // Rellenar los values antes de inicializar el editor
            this.camposEditores().forEach(f => {
                const el = document.getElementById(f);
                if (el) {
                    el.value = this.tema[f] || '';
                }
            });

            this.borrarEditores();

            tinymce.init({
                selector: 'textarea.datostema',
                height: 350,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css',
                setup: (editor) => {
                    editor.on('change', () => {
                        this.tema[editor.id] = editor.getContent();
                    });
                }
            });
        },

        borrarEditores() {
            if (!window.tinymce) return;
            this.camposEditores().forEach(id => {
                if (tinymce.get(id)) {
                    tinymce.remove(id);
                }
            });
        },

        // Sincroniza el contenido de los editores con el objeto tema
        leerEditores() {
            if (!window.tinymce) return;
            this.camposEditores().forEach(id => {
                const e = tinymce.get(id);
                if (e) {
                    e.save();
                    this.tema[id] = e.getContent();
                }
            });
        },

        // --- Carga de datos ---
        async cargarMaterias() {
            try {
                this.materias = await temasAPI.listarMaterias() || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarMateria() {
            this.idTema = 0;
            this.tema = null;
            this.selCE = {};
            this.selCom = {};
            this.borrarEditores();

            if (this.idMateria <= 0) {
                this.temas = [];
                this.ra = [];
                this.competencias = [];
                return;
            }

            await this.refrescarListado();
            await this.cargarAccordion();
        },

        async refrescarListado() {
            try {
                const data = await temasAPI.listarTemas(this.idMateria);
                this.temas = data.temas || [];
                this.horasAnuales = data.horas_anuales || 0;
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cargarAccordion() {
            try {
                const data = await temasAPI.cargarAccordionRAyCE(this.idMateria);
                this.idCiclo = data.idCiclo || 0;
                this.ra = data.ra || [];
                this.totalRA = data.total || 0;
                this.competencias = data.competencias || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Edición de un tema ---
        async editarTema(id) {
            if (id <= 0) return;
            try {
                const data = await temasAPI.obtenerTema(id);
                const t = data.tema;
                this.tema = {
                    orden: t.orden,
                    titulo: t.titulo,
                    horas: t.horas,
                    trimestre: t.trimestre,
                    peso_evaluacion: t.peso_evaluacion,
                    descripcion: t.descripcion,
                    justificacion: t.justificacion,
                    contexto: t.contexto,
                    contenidos: t.contenidos,
                    secuenciacion: t.secuenciacion,
                    recursos: t.recursos,
                    evaluacion: t.evaluacion,
                    metodologia: t.metodologia,
                    adaptaciones: t.adaptaciones,
                    contextoDefecto: !!t.contexto_defecto,
                    recursosDefecto: !!t.recursos_defecto,
                    metodologiaDefecto: !!t.metodologia_defecto,
                    adaptacionesDefecto: !!t.adaptaciones_defecto
                };

                // Pre-marcar CE según criterios del tema
                this.selCE = {};
                (data.criterios || []).forEach(c => {
                    this.selCE[this.ceKey(c.idRA, c.codigo)] = true;
                });
                // Pre-marcar competencias
                this.selCom = {};
                (data.competencias || []).forEach(idCom => {
                    this.selCom[idCom] = true;
                });

                this.idTema = id;
                this.$nextTick(() => {
                    this.inicializarEditores();
                });

                // Subir al editor
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        cerrarEdicion() {
            this.borrarEditores();
            this.idTema = 0;
            this.tema = null;
        },

        // --- Alta (nueva unidad) ---
        nuevoTema() {
            this.nuevaForm = { orden: 0, titulo: '' };
            this.modalNueva.show();
        },

        async guardarNuevo() {
            if (!this.nuevaForm.orden || !this.nuevaForm.titulo) {
                Swal.fire('Error', 'Indica el número y el título del tema', 'warning');
                return;
            }
            try {
                const res = await temasAPI.nuevo(this.idMateria, this.nuevaForm.orden, this.nuevaForm.titulo);
                this.modalNueva.hide();
                this.nuevaForm = { orden: 0, titulo: '' };
                await this.refrescarListado();
                this.editarTema(res.data.id);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Guardar ---
        // Devuelve el resultado del backend (throws en caso de fallo de red/JSON)
        async _guardarTema() {
            this.leerEditores();

            const criterios = [];
            Object.keys(this.selCE).forEach(k => {
                if (this.selCE[k]) {
                    const partes = k.split('_');
                    criterios.push({ idRA: parseInt(partes[1]), codigo: partes[2] });
                }
            });
            const competencias = Object.keys(this.selCom).filter(k => this.selCom[k]).map(k => parseInt(k));

            return await temasAPI.guardar({
                idTema: this.idTema,
                orden: this.tema.orden,
                titulo: this.tema.titulo,
                horas: this.tema.horas,
                trimestre: this.tema.trimestre,
                peso_evaluacion: this.tema.peso_evaluacion,
                descripcion: this.tema.descripcion,
                justificacion: this.tema.justificacion,
                contexto: this.tema.contexto,
                contenidos: this.tema.contenidos,
                secuenciacion: this.tema.secuenciacion,
                recursos: this.tema.recursos,
                evaluacion: this.tema.evaluacion,
                metodologia: this.tema.metodologia,
                adaptaciones: this.tema.adaptaciones,
                contexto_defecto: this.tema.contextoDefecto,
                recursos_defecto: this.tema.recursosDefecto,
                metodologia_defecto: this.tema.metodologiaDefecto,
                adaptaciones_defecto: this.tema.adaptacionesDefecto,
                criterios,
                competencias
            });
        },

        async guardar() {
            this.guardando = true;
            try {
                const res = await this._guardarTema();
                if (!res.errorTema && !res.errorCriterios && !res.errorCompetencias) {
                    Swal.fire('Éxito', 'Tema guardado correctamente', 'success');
                } else {
                    let msg = '';
                    if (res.errorTema) msg += 'Los datos generales del tema no se guardaron correctamente\n';
                    if (res.errorCriterios) msg += 'Los criterios de evaluación no se guardaron correctamente\n';
                    if (res.errorCompetencias) msg += 'Las competencias no se guardaron correctamente\n';
                    Swal.fire('Error', msg.trim(), 'error');
                }
                await this.refrescarListado();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        // --- Borrar ---
        async borrarTema(t) {
            const confirmacion = await Swal.fire({
                title: '¿Borrar tema?',
                text: `¿Confirmas el borrado del tema '${t.titulo}'?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmacion.isConfirmed) return;
            try {
                await temasAPI.borrar(t.id);
                if (this.idTema === t.id) {
                    this.cerrarEdicion();
                }
                await this.refrescarListado();
                Swal.fire('Éxito', 'Tema eliminado correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Repetir campo "Evaluación" en toda la materia ---
        async repetirEvaluacion() {
            const confirmacion = await Swal.fire({
                title: 'Copiar campo Evaluación',
                text: 'Al copiar el campo "Evaluación" en todos los temas de la materia, se sobreescribirá el contenido actual de los demás temas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, copiar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmacion.isConfirmed) return;

            let evaluacion = this.tema.evaluacion;
            const editor = window.tinymce ? tinymce.get('evaluacion') : null;
            if (editor) {
                editor.save();
                evaluacion = editor.getContent();
            }
            try {
                await temasAPI.repetirEvaluacion(this.idMateria, evaluacion);
                Swal.fire('Éxito', 'El campo "Evaluación" se ha copiado en todos los temas de la materia', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Recalcular porcentajes de los RA ---
        async calcularPorcentajes() {
            const confirmacion = await Swal.fire({
                title: 'Recalcular porcentajes',
                text: '¿Deseas recalcular y actualizar los porcentajes de evaluación de los RA asociados a esta materia? Se sobreescribirán los valores actuales.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, recalcular',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmacion.isConfirmed) return;

            this.guardando = true;
            try {
                // Como en v3: primero se guarda el tema (con sus CE) y después se recalcula
                await this._guardarTema();
                await temasAPI.recalcularPorcentajes(this.idMateria);
                await this.cargarAccordion();
                Swal.fire('Éxito', 'Porcentajes de evaluación recalculados', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        // --- RA/CE: marcar/desmarcar todos los CE de un RA ---
        ceKey(idRA, codigo) {
            return `ce_${idRA}_${codigo}`;
        },

        raTodosMarcados(id) {
            const r = this.ra.find(x => x.id === id);
            if (!r || r.ce.length === 0) return false;
            return r.ce.every(c => this.selCE[this.ceKey(id, c.codigo)]);
        },

        marcarDesmarcar(id) {
            const r = this.ra.find(x => x.id === id);
            if (!r) return;
            const nuevoEstado = !this.raTodosMarcados(id);
            r.ce.forEach(c => {
                this.selCE[this.ceKey(id, c.codigo)] = nuevoEstado;
            });
        },

        // --- Edición de un RA concreto (modal) ---
        cargarModalRA(id) {
            const r = this.ra.find(x => x.id === id);
            if (!r) return;
            this.raEdit = {
                id: r.id,
                orden: r.orden,
                texto: r.texto,
                porcentaje: r.porcentaje_evaluacion,
                esClave: !!r.es_clave
            };
            this.modalRA.show();
        },

        async guardarRA() {
            try {
                await temasAPI.actualizarRA(this.raEdit.id, this.raEdit.porcentaje, this.raEdit.esClave);
                this.modalRA.hide();
                await this.cargarAccordion();
                Swal.fire('Éxito', 'Resultado de aprendizaje actualizado', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
    }
};
