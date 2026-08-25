// Fase 6.1 — Actas de departamentos
// Fiel a v3: las actas de departamento se almacenan en actas_departamentos,
// una fila por acta (con su texto y fecha).
// - El texto se edita con TinyMCE (misma configuración que v3).
// - «Nueva acta» no crea la fila hasta pulsar «Guardar cambios»: prepara el
//   editor con el texto inicial — todos los profesores del departamento en
//   «Asistentes» y la apertura de «Orden del día» — que devuelve
//   actas/nueva.php (fiel a v3 nueva_acta_departamento.php).
// - El jefe de departamento no elige departamento: entra fijo al suyo
//   (patrón v4 de resultados_aprendizaje; en v3 era el desplegable de la
//   cabecera, que solo veía el admin).
// - «Generar PDF» abre backend/pdf/pdf_acta.php?idActa=X (fiel a v3).
const ActasView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-journal-text me-2"></i>Actas de departamentos</h2>
                    <p class="text-muted">
                        <em>Selecciona la fecha del acta que revisar, o bien introduce una nueva con el botón «Nueva acta», si eres jefe/a del departamento.</em>
                    </p>
                </div>
            </div>

            <!-- Selectores -->
            <div class="row mb-3 g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" :disabled="!esAdmin" @change="cambiarDepartamento">
                        <option value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Acta</label>
                    <select class="form-select" v-model="idActa" @change="cambiarActa">
                        <option value="">-- Selecciona una acta --</option>
                        <option v-for="a in actas" :key="a.id" :value="a.id">{{ formatearFecha(a.fecha) }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-md-end">
                    <button class="btn btn-outline-secondary" :disabled="!idActa" @click="generarPDF">
                        <i class="bi bi-filetype-pdf me-1"></i>Generar PDF
                    </button>
                </div>
            </div>

            <!-- Área de edición (jefe de departamento o admin, fiel a v3) -->
            <div class="card shadow-sm" v-if="permisos">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <button class="btn btn-outline-primary" @click="nuevaActa">
                            <i class="bi bi-plus-lg me-1"></i>Nueva acta
                        </button>
                    </div>
                    <div v-if="idActa || esNueva">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha reunión</label>
                                <input type="date" class="form-control" v-model="form.fecha">
                            </div>
                        </div>
                        <textarea id="editorActa"></textarea>
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" @click="guardar">
                                <i class="bi bi-save me-1"></i>Guardar cambios
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted py-4">
                        Selecciona una acta para revisarla, o pulsa «Nueva acta» para crear una.
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            departamentos: [],
            idDepartamento: '',
            actas: [],
            idActa: '',
            permisos: false,
            esNueva: false,
            form: { idActa: 0, fecha: '' }
        };
    },

    computed: {
        esAdmin() {
            return this.usuario.rol === 'admin';
        }
    },

    async mounted() {
        await this.cargarDepartamentos();
        if (this.esAdmin) {
            // El admin elige el departamento; nadie más lo hace
            return;
        }
        // Jefe de departamento o profesor: fijo al suyo asignado
        // (se guarda como texto para que el <option :value> coincida)
        if (this.usuario.idDepartamento) {
            this.idDepartamento = String(this.usuario.idDepartamento);
        }
        await this.cargar();
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        // --- Selectores ---
        async cargarDepartamentos() {
            // Mismos permisos de v3: jefe de departamento o admin
            this.permisos = this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento';
            try {
                this.departamentos = await DepartamentosAPI.listar() || [];
            } catch (error) {
                // Si falla, se mantiene el listado anterior
                this.departamentos = [];
            }
        },

        // El admin cambia de departamento: se limpia todo
        cambiarDepartamento() {
            this.idActa = '';
            this.esNueva = false;
            this.actas = [];
            this.form = { idActa: 0, fecha: '' };
            this.borrarEditor();
            this.cargar();
        },

        // Actas del departamento (más reciente primero)
        async cargar() {
            if (!this.idDepartamento) return;
            try {
                this.actas = await ActasAPI.listar(this.idDepartamento) || [];
            } catch (error) {
                this.actas = [];
            }
        },

        // Acta elegida en el desplegable (fiel a v3 cambiarActa):
        // carga su fecha y su texto en el editor; sin acta, lo vacía
        async cambiarActa() {
            this.esNueva = false;
            if (!this.idActa) {
                this.form = { idActa: 0, fecha: '' };
                this.borrarEditor();
                return;
            }
            try {
                const data = await ActasAPI.obtener(this.idActa);
                this.form = { idActa: this.idActa, fecha: String(data.fecha || '').slice(0, 10) };
                this.$nextTick(() => {
                    this.inicializarEditor(data.texto || '');
                });
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // Botón «Nueva acta» (fiel a v3 nuevaActa): no crea la fila todavía,
        // prepara el editor con el texto inicial del departamento
        async nuevaActa() {
            if (!this.idDepartamento) {
                Avisos.aviso('Debes seleccionar un departamento');
                return;
            }
            try {
                const data = await ActasAPI.nueva(this.idDepartamento);
                this.idActa = '';
                this.esNueva = true;
                this.form = { idActa: 0, fecha: '' };
                this.$nextTick(() => {
                    this.inicializarEditor(data.texto || '');
                });
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // --- TinyMCE (misma configuración que v3 y que el resto de la app) ---
        async inicializarEditor(texto) {
            if (!TinyMCEUtils.disponible()) {
                console.warn('TinyMCE no disponible — se muestra el textarea plano');
                return;
            }
            const area = document.querySelector('textarea#editorActa');
            if (!area) return;
            area.value = texto || '';
            await TinyMCEUtils.iniciar({
                selector: 'textarea#editorActa',
                height: 300,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css'
            }, ['editorActa']);
        },

        borrarEditor() {
            TinyMCEUtils.quitar(['editorActa']);
        },

        // Contenido del editor (guardando antes en el textarea)
        leerTexto() {
            const editor = window.tinymce ? tinymce.get('editorActa') : null;
            if (!editor) return '';
            editor.save();
            return editor.getContent();
        },

        // --- Botones ---
        // «Generar PDF» (fiel a v3): abre el PDF del acta en una pestaña
        generarPDF() {
            if (!this.idActa) {
                Avisos.aviso('Debes seleccionar una acta');
                return;
            }
            window.open('../backend/pdf/pdf_acta.php?idActa=' + this.idActa, '_blank');
        },

        // «Guardar cambios» (fiel a v3 insertar_acta_departamento):
        // sin idActa es INSERT (nueva fila), con idActa es UPDATE
        async guardar() {
            const texto = this.leerTexto();
            if (!this.form.fecha) {
                Avisos.aviso('Debes establecer una fecha para el acta');
                return;
            }
            try {
                const res = await ActasAPI.guardar({
                    idActa: this.form.idActa || 0,
                    idDepartamento: this.idDepartamento,
                    texto: texto,
                    fecha: this.form.fecha
                });
                this.form.idActa = res.data.id;
                this.idActa = res.data.id;
                // La acta nueva aparece en el desplegable (fiel a v3:
                // el formulario vuelve a cargar la lista de fechas)
                await this.cargar();
                Avisos.exito('Acta guardada', 'Datos guardados correctamente');
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // Formatea la fecha de la BD (YYYY-MM-DD HH:MM:SS) como dd/mm/aaaa
        formatearFecha(fecha) {
            if (!fecha) return '';
            const f = String(fecha).slice(0, 10);
            const partes = f.split('-');
            if (partes.length !== 3) return f;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
    }
};
