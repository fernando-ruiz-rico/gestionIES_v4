// Componente de la Fase 2.7: Contenidos por defecto de temas / unidades
// Fiel a v3 (temas_contenidos_defecto.php): un departamento = 4 campos
// (contexto, recursos, metodología, acciones). Mismo patrón que la 2.3.
const TemasContenidosDefectoView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-file-text me-2"></i>Contenidos por Defecto de Temas</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Algunos contenidos de las unidades se prestan a ser comunes para varias.
                        En esta sección se editan y mantienen para reaprovecharlos.
                        Si un profesor rellena su propio contenido, prevalecerá el contenido propio de esa unidad.
                    </div>
                </div>
            </div>

            <!-- Sección con el departamento ya elegido -->
            <div class="row" v-if="idDepartamento">
                <div class="col-12 mb-3">
                    <div class="alert alert-secondary">
                        <i class="bi bi-building me-2"></i>
                        <strong>Departamento:</strong> {{ departamentoActual ? departamentoActual.nombre : 'Desconocido' }}
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Contenidos para las unidades</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="cargando" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            <div v-else>
                                <!-- Pestañas con los apartados editables -->
                                <ul class="nav nav-tabs" id="tabsTema" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab_contexto" data-bs-toggle="tab"
                                                data-bs-target="#seccion_contexto" type="button" role="tab"
                                                aria-controls="seccion_contexto">Contexto</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab_recursos" data-bs-toggle="tab"
                                                data-bs-target="#seccion_recursos" type="button" role="tab"
                                                aria-controls="seccion_recursos">Recursos</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab_metodologia" data-bs-toggle="tab"
                                                data-bs-target="#seccion_metodologia" type="button" role="tab"
                                                aria-controls="seccion_metodologia">Metodología</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab_adaptaciones" data-bs-toggle="tab"
                                                data-bs-target="#seccion_adaptaciones" type="button" role="tab"
                                                aria-controls="seccion_adaptaciones">Adaptaciones</button>
                                    </li>
                                </ul>

                                <div class="tab-content mt-3 mb-3" id="contenidoTabs">
                                    <div class="tab-pane fade show active" id="seccion_contexto" role="tabpanel" aria-labelledby="tab_contexto">
                                        <label class="control-label" for="contexto">Contexto</label>
                                        <textarea id="contexto" class="datostema" rows="10"></textarea>
                                    </div>
                                    <div class="tab-pane fade" id="seccion_recursos" role="tabpanel" aria-labelledby="tab_recursos">
                                        <label class="control-label" for="recursos">Recursos</label>
                                        <textarea id="recursos" class="datostema" rows="10"></textarea>
                                    </div>
                                    <div class="tab-pane fade" id="seccion_metodologia" role="tabpanel" aria-labelledby="tab_metodologia">
                                        <label class="control-label" for="metodologia">Metodología</label>
                                        <textarea id="metodologia" class="datostema" rows="10"></textarea>
                                    </div>
                                    <div class="tab-pane fade" id="seccion_adaptaciones" role="tabpanel" aria-labelledby="tab_adaptaciones">
                                        <label class="control-label" for="adaptaciones">Adaptaciones</label>
                                        <textarea id="adaptaciones" class="datostema" rows="10"></textarea>
                                    </div>
                                </div>

                                <div class="mt-3 text-end">
                                    <button class="btn btn-secondary me-2" @click="limpiarTodos">
                                        <i class="bi bi-eraser me-1"></i>Limpiar todo
                                    </button>
                                    <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                                        <i class="bi bi-save me-1"></i>
                                        {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso si aún no hay departamento -->
            <div class="row" v-if="!idDepartamento && !cargando">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Selecciona un departamento para comenzar
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
            editores: {
                contexto: '',
                recursos: '',
                metodologia: '',
                acciones: ''
            },
            cargando: false,
            guardando: false
        };
    },

    computed: {
        // Un admin con departamento propio lo usa sin poder elegir;
        // un admin sin departamento elige libremente (fiel a la 2.3).
        esJefe() {
            return this.usuario.rol === 'admin' && !!this.usuario.idDepartamento;
        },
        departamentoActual() {
            return this.departamentos.find(d => String(d.id) === String(this.idDepartamento));
        }
    },

    async mounted() {
        await this.cargarDepartamentos();
        if (this.esJefe) {
            const dpto = this.departamentos.find(x => String(x.id) === String(this.usuario.idDepartamento));
            this.idDepartamento = dpto ? dpto.id : this.usuario.idDepartamento;
        }
        if (this.idDepartamento) {
            await this.cargarContenido();
        }
    },

    beforeUnmount() {
        this.borrarTodosLosEditores();
    },

    methods: {
        // -- TinyMCE (misma configuración que la 2.3) --------------------------------
        inicializarEditor(campo, texto) {
            if (!window.tinymce) {
                this.editores[campo] = texto || '';
                return;
            }
            const area = document.querySelector(`textarea#${campo}`);
            if (!area) {
                this.editores[campo] = texto || '';
                return;
            }
            area.value = texto || '';
            this.borrarEditor(campo);
            tinymce.init({
                selector: `textarea#${campo}`,
                height: 260,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css',
                setup: (editor) => {
                    editor.on('input change', () => {
                        this.editores[campo] = editor.getContent();
                    });
                }
            });
        },

        borrarEditor(campo) {
            if (window.tinymce && tinymce.get(campo)) {
                tinymce.remove(campo);
            }
        },

        inicializarTodosLosEditores() {
            this.inicializarEditor('contexto', this.editores.contexto);
            this.inicializarEditor('recursos', this.editores.recursos);
            this.inicializarEditor('metodologia', this.editores.metodologia);
            this.inicializarEditor('adaptaciones', this.editores.adaptaciones);
        },

        borrarTodosLosEditores() {
            ['contexto', 'recursos', 'metodologia', 'adaptaciones'].forEach(campo => {
                this.borrarEditor(campo);
            });
        },

        // -- Departamento / contenido ----------------------------------------------
        async cargarDepartamentos() {
            try {
                const response = await fetch('../backend/api/departamentos/listar.php');
                const data = await response.json();
                if (data) {
                    this.departamentos = data;
                }
            } catch (error) {
                console.error('Error al cargar departamentos:', error);
            }
        },

        async cargarContenido() {
            if (!this.idDepartamento) {
                return;
            }
            this.cargando = true;
            try {
                const data = await TemasContenidosDefectoAPI.cargar(this.idDepartamento);
                this.editores = {
                    contexto: data.contexto || '',
                    recursos: data.recursos || '',
                    metodologia: data.metodologia || '',
                    acciones: data.adaptaciones || ''
                };
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
            // Inicializar los editores con el contenido cargado
            this.$nextTick(() => {
                this.inicializarTodosLosEditores();
            });
        },

        cambiarDepartamento() {
            this.borrarTodosLosEditores();
            this.cargarContenido();
        },

        // -- Acción de guardar / limpiar -------------------------------------------
        async guardar() {
            // Volcar el contenido de cada editor antes de enviar
            ['contexto', 'recursos', 'metodologia', 'adaptaciones'].forEach(campo => {
                const editor = window.tinymce ? tinymce.get(campo) : null;
                if (editor) {
                    editor.save();
                    this.editores[campo] = editor.getContent();
                }
            });

            this.guardando = true;
            try {
                await TemasContenidosDefectoAPI.guardar({
                    idDepartamento: this.idDepartamento,
                    contexto: this.editores.contexto,
                    recursos: this.editores.recursos,
                    metodologia: this.editores.metodologia,
                    acciones: this.editores.adaptaciones
                });
                Swal.fire('Éxito', 'Contenidos guardados correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        limpiarTodos() {
            this.borrarTodosLosEditores();
            this.editores = {
                contexto: '',
                recursos: '',
                metodologia: '',
                acciones: ''
            };
        }
    }
};
