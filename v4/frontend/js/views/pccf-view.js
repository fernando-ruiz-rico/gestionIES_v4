// Vista del PCCF (Fase 3.1)
// Edita el contenido del Proyecto Curricular de Ciclo Formativo (PCCF) para un ciclo y apartado
const PCCFView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-file-earmark-text me-2"></i>PCCF — Proyecto Curricular de Ciclo Formativo</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Ciclo formativo</label>
                    <select class="form-select" v-model="idCiclo" @change="cambiarCiclo">
                        <option value="">-- Selección un ciclo --</option>
                        <option v-for="ciclo in ciclos" :key="ciclo.id" :value="ciclo.id">
                            {{ ciclo.nombre }}
                        </option>
                    </select>
                </div>
                <div class="row mt-2" v-if="idCiclo">
                    <div class="col-md-12">
                        <label class="form-label">Apartado</label>
                        <select class="form-select" v-model="idApartado" @change="cambiarApartado">
                            <option value="">-- Selecciona un apartado --</option>
                            <option v-for="apto in apartadosFiltrados" :key="apto.id" :value="apto.id">
                                {{ apto.rule }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botones para generar el PDF del PCCF -->
            <div class="row mb-3" v-if="idCiclo">
                <div class="col-md">
                    <button class="btn btn-light w-100" :disabled="!idApartado" @click="generarPDFApartado()" title="Genera el PDF de un ciclo y apartado concretos">
                        <i class="bi bi-filetype-pdf me-1"></i>Generar PDF de Apartado
                    </button>
                </div>
                <div class="col-md">
                    <button class="btn btn-light w-100" @click="generarPDF()" title="Genera el PDF completo del ciclo">
                        <i class="bi bi-filetype-pdf me-1"></i>Generar PDF
                    </button>
                </div>
            </div>

            <!-- Zona de edición con TinyMCE -->
            <div v-if="idApartado && idCiclo">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="cargando" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <div v-else>
                            <textarea id="editorPCCF"></textarea>
                            <div class="mt-3 text-end">
                                <button class="btn btn-secondary me-2" @click="limpiar">
                                    <i class="bi bi-eraser me-1"></i>Limpiar
                                </button>
                                <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                                    <i class="bi bi-save me-1"></i>
                                    {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3" v-if="idCiclo && !idApartado">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Selecciona un apartado para editar su contenido. El contenido se guarda por ciclo y apartado.
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
            ciclos: [],
            apartados: [],
            idCiclo: '',
            idApartado: '',
            contenido: '',
            cargando: false,
            guardando: false
        };
    },

    computed: {
        // Numeración de apartados idéntica a v3 (cont++ / cont2++)
        apartadosFiltrados() {
            let cont = 0;
            let cont2 = 0;
            const resultado = [];
            for (const apto of this.apartados) {
                if (!Number(apto.subapartado)) {
                    cont++;
                    cont2 = 0;
                    apto.rule = `${cont}. ${apto.titulo}`;
                } else {
                    cont2++;
                    apto.rule = `${cont}.${cont2}. ${apto.titulo}`;
                }
                resultado.push(apto);
            }
            return resultado;
        }
    },

    async mounted() {
        try {
            this.ciclos = await PCCFAPI.listarCiclos();
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        async inicializarEditor(texto) {
            if (!TinyMCEUtils.disponible()) {
                console.warn('TinyMCE no disponible — se muestra el textarea plano');
                return;
            }
            const ids = ['editorPCCF'];
            // TinyMCE 7: init y remove son asíncronos; hay que esperar a que
            // la destrucción de la instancia anterior termine de verdad.
            await TinyMCEUtils.quitar(ids);
            const area = document.querySelector('textarea#editorPCCF');
            if (!area) return;
            // TinyMCE 7 lee el contenido inicial desde el valor del textarea
            area.value = texto || '';
            await TinyMCEUtils.iniciar({
                selector: 'textarea#editorPCCF',
                height: 400,
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
            return TinyMCEUtils.quitar(['editorPCCF']);
        },

        cambiarCiclo() {
            this.idApartado = '';
            this.borrarEditor();
            this.contenido = '';
            this.cargarApartados();
        },

        async cargarApartados() {
            if (!this.idCiclo) return;
            try {
                this.apartados = await PCCFApartadosAPI.listar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        cambiarApartado() {
            this.cargando = true;
            this.$nextTick(() => {
                this.cargarContenido();
            });
        },

        async cargarContenido() {
            if (!this.idApartado || !this.idCiclo) return;
            try {
                const data = await PCCFAPI.listar(this.idCiclo, this.idApartado);
                this.contenido = data.texto || '';
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.cargando = false;
            }
            this.$nextTick(() => {
                this.inicializarEditor(this.contenido);
            });
        },

        async guardar() {
            const editor = window.tinymce ? tinymce.get('editorPCCF') : null;
            if (editor) {
                editor.save();
                this.contenido = editor.getContent();
            }
            this.guardando = true;
            try {
                await PCCFAPI.guardar(this.idCiclo, this.idApartado, this.contenido);
                Avisos.exito('Éxito', 'Contenido guardado correctamente');
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.guardando = false;
            }
        },

        // Genera el PDF completo del PCCF del ciclo (sin apartado)
        generarPDF() {
            if (!this.idCiclo) {
                Avisos.aviso('Deves seleccionar un ciclo');
                return;
            }
            window.open(
                `../backend/api/pccf/generar.php?modo=completo&idCiclo=${this.idCiclo}`,
                '_blank'
            );
        },

        // Genera el PDF de un ciclo y apartado concretos
        generarPDFApartado() {
            if (!this.idCiclo || !this.idApartado) {
                Avisos.aviso('Deves seleccionar un ciclo y un apartado');
                return;
            }
            window.open(
                `../backend/api/pccf/generar.php?modo=apartado&idCiclo=${this.idCiclo}&idApartado=${this.idApartado}`,
                '_blank'
            );
        },

        limpiar() {
            this.contenido = '';
            const editor = window.tinymce ? tinymce.get('editorPCCF') : null;
            if (editor) {
                editor.setContent('');
            }
        }
    }
};
