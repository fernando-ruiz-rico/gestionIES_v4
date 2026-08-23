// Vista de los contenidos por defect del PCCF (Fase 3.3)
// Edita el contenido por defecto de un apartado para un departamento
const PCCFContenidosDefectoView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-database me-2"></i>Contenidos por defecto del PCCF</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cambiarDepartamento">
                        <option value="">-- Selección un departamento --</option>
                        <option v-for="depto in departamentos" :key="depto.id" :value="depto.id">
                            {{ depto.nombre }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6 mt-2" v-if="idDepartamento">
                    <label class="form-label">Apartado</label>
                    <select class="form-select" v-model="idApartado" @change="cargarContenido">
                        <option value="">-- Selección un apartado --</option>
                        <option v-for="apto in apartadosFiltrados" :key="apto.id" :value="apto.id">
                            {{ apto.rule }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="idApartado && idDepartamento">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="cargando" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <div v-else>
                            <textarea id="editorPCCFDefecto"></textarea>
                            <div class="mt-3 text-end">
                                <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                                    <i class="bi bi-save me-1"></i>
                                    {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3" v-if="idDepartamento && !idApartado">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Selecciona un apartado para editar su contenido por defecto para el departamento.
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
            apartados: [],
            idDepartamento: '',
            idApartado: '',
            contenido: '',
            cargando: false,
            guardando: false
        };
    },

    computed: {
        // Recorremos todos los apartados para mantener la numeración original,
        // pero sólo mostramos en la lista los que admitan contenido por defecto
        // y sean editables (tipo == 0): los de otro tipo se rellenan
        // automáticamente a partir de la base de datos y no se editan en esta
        // opción (fiel a v3, pccf_contenidos_defecto.php).
        apartadosFiltrados() {
            let cont = 0;
            let cont2 = 0;
            const resultado = [];
            for (const apto of this.apartados) {
                const sub = Number(apto.subapartado);
                if (!sub) {
                    cont++;
                    cont2 = 0;
                } else {
                    cont2++;
                }
                if (apto.contenido_defecto && apto.tipo == 0) {
                    apto.rule = sub
                        ? `${cont}.${cont2}. ${apto.titulo}`
                        : `${cont}. ${apto.titulo}`;
                    resultado.push(apto);
                }
            }
            return resultado;
        }
    },

    async mounted() {
        try {
            const response = await fetch('../backend/api/departamentos/listar.php');
            this.departamentos = await response.json();
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
            const ids = ['editorPCCFDefecto'];
            // TinyMCE 7: init y remove son asíncronos; hay que esperar a que
            // la destrucción de la instancia anterior termine de verdad.
            await TinyMCEUtils.quitar(ids);
            const area = document.querySelector('textarea#editorPCCFDefecto');
            if (!area) return;
            area.value = texto || '';
            await TinyMCEUtils.iniciar({
                selector: 'textarea#editorPCCFDefecto',
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
            return TinyMCEUtils.quitar(['editorPCCFDefecto']);
        },

        cambiarDepartamento() {
            this.idApartado = '';
            this.borrarEditor();
            this.contenido = '';
            this.cargarApartados();
        },

        async cargarApartados() {
            if (!this.idDepartamento) return;
            try {
                this.apartados = await PCCFApartadosAPI.listar();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cargarContenido() {
            if (!this.idApartado || !this.idDepartamento) return;
            this.cargando = true;
            try {
                const data = await PCCFContenidosDefectoAPI.cargar(this.idApartado, this.idDepartamento);
                this.contenido = data.texto || '';
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
            this.$nextTick(() => {
                this.inicializarEditor(this.contenido);
            });
        },

        async guardar() {
            const editor = window.tinymce ? tinymce.get('editorPCCFDefecto') : null;
            if (editor) {
                editor.save();
                this.contenido = editor.getContent();
            }
            this.guardando = true;
            try {
                await PCCFContenidosDefectoAPI.guardar(this.idApartado, this.idDepartamento, this.contenido);
                Swal.fire('Éxito', 'Contenido guardado correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        }
    }
};
