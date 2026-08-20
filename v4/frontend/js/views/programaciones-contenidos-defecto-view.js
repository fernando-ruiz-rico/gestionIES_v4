const ProgramacionesContenidosDefectoView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-file-text me-2"></i>Contenidos por Defecto de Programaciones</h2>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Algunos contenidos de las programaciones se prestan a ser comunes para varias materias. 
                        En esta sección puedes editarlos y mantenerlos para que se puedan reaprovechar.
                        El contenido por defecto prevalecerá si un profesor no rellena su propio contenido.
                    </div>
                </div>
            </div>

            <div class="row mb-3" v-if="departamentos.length > 0">
                <div class="col-md-6" v-if="!esJefe">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cambiarDepartamento">
                        <option value="">--Selecciona un departamento--</option>
                        <option v-for="depto in departamentos" :key="depto.id" :value="depto.id">
                            {{ depto.nombre }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6" v-else>
                    <label class="form-label">Departamento</label>
                    <select class="form-select" disabled>
                        <option>{{ departamentoActual ? departamentoActual.nombre : 'Desconocido' }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apartado</label>
                    <select class="form-select" v-model="idApartado" @change="cargarContenido">
                        <option value="">--Selecciona un apartado--</option>
                        <option v-for="apto in apartadosDisponibles" :key="apto.id" :value="apto.id">
                            {{ apto.tituloMostrar }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="row" v-if="idDepartamento && idApartado">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ apartadoActual }}</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="cargando" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            <div v-else>
                                <textarea id="editorContenidoDefecto"></textarea>
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
            </div>

            <div class="row" v-else-if="!idDepartamento">
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
            apartados: [],
            idDepartamento: '',
            idApartado: '',
            contenido: '',
            apartadoActual: '',
            cargando: false,
            guardando: false
        };
    },

    computed: {
        apartadosDisponibles() {
            return this.apartados.filter(a => a.contenido_defecto && a.tipo == 0);
        },
        // Un jefe de departamento (rol 'admin' con su propio idDepartamento)
        // queda fijo al suyo; un admin sin departamento elige libremente.
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
        await this.cargarApartados();
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        // TinyMCE (misma configuración que v3: initTinyMCE('progeditar'))
        inicializarEditor(texto) {
            if (!window.tinymce) return;
            this.borrarEditor();
            tinymce.init({
                selector: 'textarea#editorContenidoDefecto',
                height: 300,
                resize: true,
                plugins: 'autolink lists advlist code fullscreen wordcount',
                toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
                statusbar: true,
                menubar: false,
                branding: false,
                content_css: 'css/estilos_tiny.css',
                value: texto || '',
                setup: (editor) => {
                    editor.on('change', () => {
                        this.contenido = editor.getContent();
                    });
                }
            });
        },

        borrarEditor() {
            if (window.tinymce && tinymce.get('editorContenidoDefecto')) {
                tinymce.remove('editorContenidoDefecto');
            }
        },

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

        cambiarDepartamento() {
            this.borrarEditor();
            this.idApartado = '';
            this.contenido = '';
            this.cargarApartados();
        },

        async cargarApartados() {
            if (!this.idDepartamento) return;

            try {
                const data = await programacionesApartadosAPI.listar();
                if (data) {
                    this.apartados = data;
                    // Calcular numeración (los valores llegan como texto desde la API)
                    let cont = 0;
                    let cont2 = 0;
                    this.apartados.forEach(apto => {
                        if (!Number(apto.subapartado)) {
                            cont++;
                            cont2 = 0;
                            apto.tituloMostrar = `${cont}. ${apto.titulo}`;
                        } else {
                            cont2++;
                            apto.tituloMostrar = `${cont}.${cont2}. ${apto.titulo}`;
                        }
                    });
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        async cargarContenido() {
            if (!this.idApartado) return;
            
            this.cargando = true;
            try {
                const apto = this.apartados.find(a => a.id === this.idApartado);
                this.apartadoActual = apto ? apto.tituloMostrar : '';
                
                const data = await programacionesContenidosDefectoAPI.cargar(this.idApartado, this.idDepartamento);
                this.contenido = data.texto || '';
                this.$nextTick(() => {
                    this.inicializarEditor(this.contenido);
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        async guardar() {
            const editor = window.tinymce ? tinymce.get('editorContenidoDefecto') : null;
            if (editor) {
                editor.save();
                this.contenido = editor.getContent();
            }
            this.guardando = true;
            try {
                await programacionesContenidosDefectoAPI.guardar(this.idApartado, this.idDepartamento, this.contenido);
                Swal.fire('Éxito', 'Contenido guardado correctamente', 'success');
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.guardando = false;
            }
        },

        limpiar() {
            this.contenido = '';
            const editor = window.tinymce ? tinymce.get('editorContenidoDefecto') : null;
            if (editor) {
                editor.setContent('');
            }
        }
    }
};
