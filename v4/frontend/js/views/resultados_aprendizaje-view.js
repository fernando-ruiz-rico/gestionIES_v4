// Fase 4.1 — Resultados de Aprendizaje (RA) por materia, fiel a v3
// Los RA se asocian a cada materia, con % de atención en empresa y % de
// evaluación, y pueden llevar asociados criterios de evaluación (CE).
//
// Flujo fiel a v3 (resultados_aprendizaje.php):
//   - El admin elige el departamento con el que trabajar; el jefe de
//     departamento y el profesor tienen el suyo asignado (no eligen).
//   - Luego se elige materia de las disponibles: el profesor solo ve las
//     suyas (en los escenarios actuales) y el jefe/admin, todas las del
//     departamento, siempre con programación activa.
//   - Botones de PDF fieles a las vistas de solo lectura de v3:
//       "resumen" : resumen general (resultados_aprendizaje_vista_previa.php)
//       "ra"      : RAs empresa (resultados_aprendizaje_vista_previa_empresa.php)
//       "ce"      : CEs empresa (criterios_evaluacion_vista_previa_empresa.php)
const ResultadosArendizajeView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-book me-2"></i>Resultados de Aprendizaje</h2>
                    <p class="text-muted">
                        <em>Haz clic en el icono del lápiz para editar los datos de cada resultado, en el de árbol para asociar criterios de evaluación, y en el de medalla para fijar qué resultados son clave y qué porcentaje de evaluación tienen.</em>
                    </p>
                </div>
            </div>

            <!-- Departamento: el admin elige; el jefe/profesor, el suyo asignado -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" :disabled="!esAdmin" @change="cambiarDepartamento">
                        <option v-if="esAdmin" value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Sin departamento elegido aún (admin) -->
            <div v-if="!listo" class="text-center text-muted py-4">
                Selecciona un departamento para empezar.
            </div>

            <template v-else>
                <!-- Botones de PDF (fieles a las vistas de solo lectura de v3) -->
                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-secondary" @click="abrirPDF('resumen')" title="Resumen general con % de empresa y totales de horas">
                            <i class="bi bi-file-earmark-text me-1"></i>Ver resumen general
                        </button>
                        <button class="btn btn-outline-secondary" @click="abrirPDF('ra')" title="RAs con formación en empresa">
                            <i class="bi bi-file-earmark-text me-1"></i>RAs empresa
                        </button>
                        <button class="btn btn-outline-secondary" @click="abrirPDF('ce')" title="CE de RA con formación en empresa">
                            <i class="bi bi-file-earmark-text me-1"></i>CEs empresa
                        </button>
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
                    <div class="col-md-8" v-if="idMateriaSeleccionada">
                        <div class="form-inline">
                            <label class="form-label me-2">Horas a impartir en empresa:</label>
                            <input type="number" class="form-control" style="width:100px" v-model="horasEmpresa" :disabled="!permisos">
                            <button class="btn btn-outline-primary ms-2" v-if="permisos" @click="actualizarHoras">Actualizar</button>
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
                                    <button class="btn btn-sm btn-outline-secondary" @click="abrirEvaluar(r)" title="Fijar % de evaluación y RA clave">
                                        <i class="bi bi-medal"></i>
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
            </template>

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
                                <label class="form-label">Porcentaje de docencia asignado a la empresa</label>
                                <select class="form-select" v-model.number="form.porcentaje_empresa">
                                    <option :value="0">0%</option>
                                    <option v-for="p in pasosPorcentaje" :key="p" :value="p">{{ p }} %</option>
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

    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            departamentos: [],
            idDepartamento: '',
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

    computed: {
        esAdmin() {
            return this.usuario.rol === 'admin';
        },
        // El departamento está listo: elegido (admin) o asignado (jefe/profesor)
        listo() {
            return !!this.idDepartamento;
        },
        // Porcentajes del desplegable de % de empresa (como el select de v3)
        pasosPorcentaje() {
            const pasos = [];
            for (let i = 5; i <= 100; i += 5) pasos.push(i);
            return pasos;
        }
    },

    async mounted() {
        this.modalRA = new bootstrap.Modal(document.getElementById('modalRA'));
        this.modalEvaluar = new bootstrap.Modal(document.getElementById('modalEvaluar'));
        this.modalCriterios = new bootstrap.Modal(document.getElementById('modalCriterios'));
        await this.cargarDepartamentos();
        if (this.esAdmin) {
            // El admin elige el departamento; nadie más lo hace
            return;
        }
        // Jefe de departamento o profesor: el departamento es el asignado
        // (se guarda como texto para que el <option :value> coincida)
        this.idDepartamento = String(this.usuario.idDepartamento);
        await this.cargarMaterias();
    },

    methods: {
        async cargarDepartamentos() {
            try {
                this.departamentos = await DepartamentosAPI.listar() || [];
            } catch (e) {
                this.departamentos = [];
            }
        },

        // El admin elige el departamento: se limpian las materias y RAs
        cambiarDepartamento() {
            this.idMateriaSeleccionada = '';
            this.resultados = [];
            this.permisos = false;
            this.cargarMaterias();
        },

        // Materias disponibles, fiel a v3:
        //   - jefe/admin: todas las del departamento con programación activa
        //   - profesor: solo las suyas en los escenarios actuales
        async cargarMaterias() {
            if (!this.idDepartamento) return;
            this.idMateriaSeleccionada = '';
            this.resultados = [];
            this.permisos = false;
            try {
                this.materias = await ResultadosArendizajeAPI.listar_materias(this.idDepartamento) || [];
            } catch (e) {
                this.materias = [];
            }
        },

        async cargar() {
            if (!this.idMateriaSeleccionada) return;
            this.cargando = true;
            try {
                const data = await ResultadosArendizajeAPI.cargar(this.idMateriaSeleccionada);
                this.resultados = data.resultados;
                this.permisos = data.permisos;
                this.horasEmpresa = data.horas_empresa;
            } catch (error) {
                Avisos.error(error.message);
                this.resultados = [];
            } finally {
                this.cargando = false;
            }
        },

        // Abre el PDF generado en el backend (misma petición directa que v3)
        abrirPDF(modo) {
            window.open('../backend/pdf_resultados_aprendizaje.php?modo=' + modo, '_blank');
        },

        async actualizarHoras() {
            if (!this.idMateriaSeleccionada) return;
            try {
                await ResultadosArendizajeAPI.actualizar_horas({
                    idMateria: this.idMateriaSeleccionada,
                    horas: this.horasEmpresa
                });
                Avisos.exito('Horas de empresa actualizadas');
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        abrirModal(r) {
            this.form = {
                id: r.id,
                texto: r.texto,
                orden: r.orden,
                porcentaje_empresa: r.porcentaje_empresa
            };
            this.esEdicion = true;
            this.modalRA.show();
        },

        abrirNuevo() {
            this.form = { id: 0, texto: '', orden: this.resultados.length + 1, porcentaje_empresa: 0 };
            this.esEdicion = false;
            this.modalRA.show();
        },

        async guardar() {
            try {
                await ResultadosArendizajeAPI.guardar({
                    id: this.form.id,
                    idMateria: this.idMateriaSeleccionada,
                    texto: this.form.texto,
                    orden: this.form.orden,
                    porcentaje_empresa: this.form.porcentaje_empresa
                });
                Avisos.exito('Guardado');
                this.modalRA.hide();
                this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        abrirEvaluar(r) {
            this.evalForm = {
                idResultado: r.id,
                porcentaje_evaluacion: r.porcentaje_evaluacion,
                es_clave: r.es_clave == 1
            };
            this.modalEvaluar.show();
        },

        async actualizarEvaluar() {
            try {
                await ResultadosArendizajeAPI.actualizar_evaluacion(this.evalForm);
                Avisos.exito('Evaluación actualizada');
                this.modalEvaluar.hide();
                this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async abrirCriterios(r) {
            this.idRAActual = r.id;
            await this.cargarCriterios();
            this.nuevoCriterio = { codigo: '', texto: '' };
            this.modalCriterios.show();
        },

        async cargarCriterios() {
            try {
                this.criterios = await ResultadosArendizajeAPI.cargar_criterios(this.idRAActual) || [];
            } catch (error) {
                // Si falla, se mantiene el listado anterior
            }
        },

        async eliminar(r) {
            const conf = await Avisos.confirmar('¿Eliminar resultado?', r.texto);
            if (conf.isConfirmed) {
                try {
                    await ResultadosArendizajeAPI.eliminar(r.id);
                    Avisos.exito('Eliminado');
                    this.cargar();
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        },

        async guardarCriterio() {
            try {
                await ResultadosArendizajeAPI.guardar_criterio({
                    idResultado: this.idRAActual,
                    codigo: this.nuevoCriterio.codigo,
                    texto: this.nuevoCriterio.texto
                });
                Avisos.exito('Criterio guardado');
                this.nuevoCriterio = { codigo: '', texto: '' };
                this.cargarCriterios();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async actualizarCriterio(c) {
            try {
                await ResultadosArendizajeAPI.actualizar_criterio({
                    idResultado: c.idRA,
                    codigo: c.codigo,
                    nuevoCodigo: c.codigo,
                    nuevoTexto: c.texto
                });
                Avisos.exito('Criterio actualizado');
                this.cargarCriterios();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async eliminarCriterio(c) {
            try {
                await ResultadosArendizajeAPI.eliminar_criterio({
                    idResultado: c.idRA,
                    codigo: c.codigo
                });
                Avisos.exito('Criterio eliminado');
                this.cargarCriterios();
            } catch (error) {
                Avisos.error(error.message);
            }
        }
    }
};
