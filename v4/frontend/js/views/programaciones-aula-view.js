const ProgramacionesAulaView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-house-door me-2"></i>Programaciones de Aula</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Introduce el texto de introducción para cada grupo en las programaciones de aula.
                        Si se deja vacío, se utilizará un texto por defecto al generar la documentación.
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

            <!-- Selectores de materia y grupo -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="selectorMateria" class="form-label">Materia</label>
                    <select id="selectorMateria" class="form-select" v-model="idMateria" @change="cambiarMateria">
                        <option :value="0">--Selecciona una materia--</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }} ({{ m.nomCurso }})</option>
                    </select>
                </div>

                <div class="col-md-6">
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
                    <button class="btn btn-light" :disabled="!idMateria || !idGrupo" @click="generarPDFSeparataCE()">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF Separata CE
                    </button>
                </div>

                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <button class="btn btn-light" :disabled="!idMateria || !idGrupo" @click="generarPDF()">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF Programación de Aula
                    </button>
                </div>
            </div>

            <!-- Editor -->
            <div class="row mt-4" v-if="idMateria > 0 && idGrupo > 0">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Texto de introducción</h5>
                        </div>
                        <div class="card-body">
                            <label for="editorAula" class="form-label mb-2">
                                Texto de introducción (opcional). Si se deja vacío, se utilizará un texto por defecto.
                            </label>
                            <textarea id="editorAula"></textarea>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                                    <i class="bi bi-save me-1"></i>
                                    {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso si no hay selección -->
            <div class="row mt-4" v-if="!idMateria">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Selecciona una materia y un grupo para comenzar.
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
            materias: [],
            grupos: [],
            temas: [],
            idMateria: 0,
            idGrupo: 0,
            idTema: 0,
            guardando: false,
            contenido: ''
        };
    },

    computed: {
        esAdmin() {
            return this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento';
        }
    },

    async mounted() {
        if (this.esAdmin) {
            await this.cargarProfesores();
        } else {
            // Un profesor usa siempre su propio id; no necesita desplegable
            this.idProfesor = 0; // El backend usa la sesión
        }
        await this.cargarMaterias();
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        // --- TinyMCE (misma configuración que v3) ---
        async inicializarEditor(texto) {
            if (!TinyMCEUtils.disponible()) {
                console.warn('TinyMCE no disponible — se muestra el textarea plano');
                return;
            }
            const ids = ['editorAula'];
            // TinyMCE 7: init y remove son asíncronos; hay que esperar a que
            // la destrucción de la instancia anterior termine de verdad.
            await TinyMCEUtils.quitar(ids);
            const area = document.querySelector('textarea#editorAula');
            if (!area) return;
            area.value = texto || '';
            await TinyMCEUtils.iniciar({
                selector: 'textarea#editorAula',
                height: 300,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css',
                setup: (editor) => {
                    editor.on('change', () => {
                        this.contenido = editor.getContent();
                    });
                }
            }, ids);
        },

        borrarEditor() {
            return TinyMCEUtils.quitar(['editorAula']);
        },

        // --- Carga de datos ---
        async cargarProfesores() {
            try {
                this.profesores = await programacionesAulaAPI.cargarProfesores() || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarProfesor() {
            this.idMateria = 0;
            this.idGrupo = 0;
            await this.cargarMaterias();
        },

        async cargarMaterias() {
            if (this.esAdmin && this.idProfesor <= 0) return;
            try {
                const data = await programacionesAulaAPI.cargarMaterias(this.idProfesor);
                this.materias = data || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarMateria() {
            this.idGrupo = 0;
            this.borrarEditor();
            if (this.idMateria <= 0) return;

            try {
                const data = await programacionesAulaAPI.cargarGrupos(this.idMateria, this.idProfesor);
                this.grupos = data || [];
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cambiarGrupo() {
            if (this.idMateria <= 0 || this.idGrupo <= 0) return;

            try {
                const data = await programacionesAulaAPI.cargarContenido(this.idTema, this.idGrupo, this.idProfesor);
                this.contenido = data.texto || '';
                this.$nextTick(() => {
                    this.inicializarEditor(this.contenido);
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        // --- Guardar ---
        async guardar() {
            let texto = this.contenido;

            const editor = window.tinymce ? tinymce.get('editorAula') : null;
            if (editor) {
                editor.save();
                texto = editor.getContent();
            }

            this.guardando = true;
            try {
                await programacionesAulaAPI.guardar(this.idTema, this.idGrupo, this.idProfesor, texto);
                Swal.fire('Éxito', 'Contenido guardado correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        // --- PDFs (pendientes Fase 8) ---
        generarPDF() {
            if (this.idMateria <= 0 || this.idGrupo <= 0) return;
            Swal.fire({
                title: 'Pendiente',
                text: 'La generación de PDF está pendiente (Fase 8)',
                icon: 'info'
            });
        },

        generarPDFSeparataCE() {
            if (this.idMateria <= 0 || this.idGrupo <= 0) return;
            Swal.fire({
                title: 'Pendiente',
                text: 'La generación de PDF está pendiente (Fase 8)',
                icon: 'info'
            });
        }
    }
};
