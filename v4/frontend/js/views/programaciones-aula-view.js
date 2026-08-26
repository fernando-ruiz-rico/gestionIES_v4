// FASE 2.4 — Programaciones de aula (opción propia de v4)
//
// Se elige un grupo (y profesor, en caso de jefe/admin) y una materia
// (las que imparte el profesor en ese grupo y tienen programación). El
// botón «Importar propuesta» hace una copia, para ese profesor y grupo, de
// la propuesta pedagógica de la materia —que debe estar marcada como
// terminada— y la muestra en este editor. A partir de ahí se trabaja con la
// copia (se modifica igual que la propuesta pedagógica, apartado a
// apartado); la propuesta pedagógica nunca se toca.
const ProgramacionesAulaView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-collection-measure me-2"></i>Programaciones de aula</h2>
                </div>
            </div>

            <!-- Selectores: profesor (solo jefe/admin), grupo, materia -->
            <div class="row mb-3">
                <div class="col-md-4" v-if="esAdmin">
                    <label class="form-label">Profesor</label>
                    <select class="form-select" v-model="idProfesor" @change="cambiarProfesor">
                        <option :value="0">--Selecciona un profesor--</option>
                        <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                    </select>
                </div>

                <div :class="esAdmin ? 'col-md-4' : 'col-md-6'">
                    <label class="form-label">Grupo</label>
                    <select class="form-select" v-model="idGrupo" @change="cambiarGrupo" :disabled="esAdmin && idProfesor <= 0">
                        <option :value="0">--Selecciona un grupo--</option>
                        <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
                    </select>
                </div>

                <div :class="esAdmin ? 'col-md-4' : 'col-md-6'">
                    <label class="form-label">Materia</label>
                    <select class="form-select" v-model="idMateria" @change="cambiarMateria" :disabled="idGrupo <= 0">
                        <option :value="0">--Selecciona una materia--</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Apartado -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Apartado</label>
                    <select class="form-select" v-model="idApartado" @change="cambiarApartado" :disabled="idMateria <= 0">
                        <option :value="0">--Selecciona un apartado--</option>
                        <option v-for="a in apartados" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Botón importar (v4 propia) -->
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <button class="btn btn-primary w-100" :disabled="!puedeImportar" @click="importarPropuesta" :title="importarTitle">
                        <i class="bi bi-download me-1"></i>Importar propuesta
                    </button>
                </div>
                <div class="col" v-if="idMateria > 0 && !terminada">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        La propuesta pedagógica de esta materia no está marcada como <em>terminada</em>;
                        hay que terminarla en la opción «Propuesta Pedagógica» para poder importarla.
                    </div>
                </div>
            </div>

            <!-- Editor (apartado editable) -->
            <div v-if="esEditable" class="mt-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar apartado de la programación de aula</h5>
                    </div>
                    <div class="card-body">
                        <textarea id="editorAula"></textarea>
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" :disabled="guardando" @click="guardar">
                                <i class="bi bi-save me-1"></i>
                                {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso (apartado automático) -->
            <div v-if="idMateria > 0 && idApartado > 0 && !esEditable">
                <div class="alert alert-info">
                    <em>El apartado seleccionado se genera automáticamente a partir de la información
                    introducida en secciones como las Unidades de Programación o los Resultados de
                    Aprendizaje. Para modificarlo, realiza los cambios en esas secciones.</em>
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
            grupos: [],
            idGrupo: 0,
            materias: [],
            idMateria: 0,
            apartados: [],
            idApartado: 0,
            tipoApartado: -1,
            guardando: false,
            contenido: ''
        };
    },

    computed: {
        esAdmin() {
            return this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento';
        },
        esEditable() {
            return this.idMateria > 0 && this.idApartado > 0 && this.tipoApartado === 0;
        },
        materiaActual() {
            return this.materias.find(m => m.id === this.idMateria) || null;
        },
        terminada() {
            return !!(this.materiaActual && this.materiaActual.terminada);
        },
        puedeImportar() {
            return this.idMateria > 0 && this.idGrupo > 0 && this.terminada;
        },
        importarTitle() {
            if (this.idMateria <= 0) {
                return 'Importar la propuesta pedagógica de la materia';
            }
            if (!this.terminada) {
                return 'La propuesta pedagógica no está marcada como terminada';
            }
            return 'Hace una copia de la propuesta pedagógica para este profesor y grupo';
        }
    },

    async mounted() {
        // Un profesor no elige profesor: el backend usa la sesión.
        if (this.esAdmin) {
            await this.cargarProfesores();
        } else {
            this.idProfesor = 0;
        }
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        // --- TinyMCE (misma configuración que la propuesta pedagógica) ---
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
            return TinyMCEUtils.quitar(['editorAula']);
        },

        // --- Carga de datos ---
        async cargarProfesores() {
            try {
                this.profesores = await ProgramacionesAulaAPI.cargarProfesores() || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async cambiarProfesor() {
            this.idGrupo = 0;
            this.idMateria = 0;
            this.idApartado = 0;
            this.apartados = [];
            this.materias = [];
            this.grupos = [];
            await this.borrarEditor();
            if (this.idProfesor <= 0) return;
            try {
                this.grupos = await ProgramacionesAulaAPI.cargarGrupos(this.idProfesor) || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async cambiarGrupo() {
            this.idMateria = 0;
            this.idApartado = 0;
            this.apartados = [];
            this.materias = [];
            await this.borrarEditor();
            if (this.idGrupo <= 0) return;
            try {
                this.materias = await ProgramacionesAulaAPI.cargarMaterias(this.idProfesor, this.idGrupo) || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async cambiarMateria() {
            await this.borrarEditor();
            this.idApartado = 0;
            this.tipoApartado = -1;
            this.apartados = [];
            if (this.idMateria <= 0) return;
            try {
                this.apartados = await ProgramacionesAulaAPI.cargarApartados(this.idMateria) || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async cambiarApartado() {
            await this.borrarEditor();
            this.contenido = '';

            if (this.idApartado <= 0) {
                this.tipoApartado = -1;
                return;
            }

            const apartado = this.apartados.find(a => a.id === this.idApartado);
            this.tipoApartado = apartado ? apartado.tipo : -1;

            if (this.tipoApartado === 0) {
                try {
                    const data = await ProgramacionesAulaAPI.cargarContenido(this.idMateria, this.idApartado, this.idGrupo, this.idProfesor);
                    this.contenido = (data && data.texto) || '';
                    this.$nextTick(() => {
                        this.inicializarEditor(this.contenido);
                    });
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        },

        // --- Importar (v4 propia) ---
        // Copia, para el profesor y el grupo elegidos, de la propuesta
        // pedagógica de la materia (debe estar terminada).
        async importarPropuesta() {
            if (!this.puedeImportar) return;

            const result = await Swal.fire({
                title: '¿Confirmar importación?',
                html: '<p>Se creará una copia, para el profesor y el grupo elegidos, de la propuesta pedagógica de la materia.</p>'
                       + '<p class="text-danger"><strong>Si ya existe una programación de aula para esa combinación, se reemplazará.</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, importar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            try {
                await ProgramacionesAulaAPI.importar(this.idMateria, this.idGrupo, this.idProfesor);
                Avisos.exito('Éxito', 'Programación de aula creada a partir de la propuesta pedagógica');
                // Mostrar la copia en el editor: si el apartado elegido no
                // es editable (o no hay ninguno), se elige el primero editable
                const apSel = this.apartados.find(a => a.id === this.idApartado);
                if (!apSel || apSel.tipo !== 0) {
                    const editable = this.apartados.find(a => a.tipo === 0);
                    if (editable) {
                        this.idApartado = editable.id;
                    }
                }
                await this.cambiarApartado();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // --- Guardar ---
        async guardar() {
            if (!this.esEditable) return;

            let texto = this.contenido;
            const editor = window.tinymce ? tinymce.get('editorAula') : null;
            if (editor) {
                editor.save();
                texto = editor.getContent();
            }

            this.guardando = true;
            try {
                const res = await ProgramacionesAulaAPI.guardar(this.idMateria, this.idApartado, this.idGrupo, this.idProfesor, texto);
                if (res && res.sin_cambios) {
                    Avisos.aviso('El contenido ya estaba guardado así (no se han realizado cambios).');
                } else {
                    Avisos.exito('Éxito', 'Contenido guardado correctamente');
                }
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.guardando = false;
            }
        }
    }
};
