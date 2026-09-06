// FASE 2.1 — Propuesta Pedagógica (edición fiel a v3/programaciones.php).
// "Programaciones" de v3: la programación de la materia, editada por
// apartados. En v4 se renombra la opción a «Propuesta Pedagógica» y se
// añade el flag "terminada" (v4 propia), que habilita importar la
// programación de aula a partir de ella.
//
// Cada materia guarda su programación como apartados + contenidos. Aquí se
// elige materia y apartado:
//   - Si el apartado es editable (tipo 0) se muestra el editor TinyMCE y se
//     carga/guarda su texto (v3: cargar_contenido/insertar_contenido).
//   - Si es de tipo automático se muestra el aviso (se genera a partir de
//     otras secciones: unidades, RA/CE...).
//
// Botones (igual que v3):
//   - Cont. defecto Unidades  → navegación a la opción de contenidos por defecto
//   - Unidades                → navegación a la opción de Temas/Unidades
//   - PDF de Unidades         → pdf_unidades_programacion.php
//   - PDF de Apartado         → pdf_programaciones_apartado.php (o de unidades si
//                                el apartado es de tipo TEMAS, 13)
//   - PDF Completo            → pdf_programaciones.php
//   - Importar                → modal (solo admin)
const ProgramacionesView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-journal-bookmark me-2"></i>Edición de Propuesta Pedagógica</h2>
                </div>
            </div>

            <!-- Selectores de materia y apartado -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Materia</label>
                    <select class="form-select" v-model="idMateria" @change="cambiarMateria">
                        <option :value="0">--Selecciona una materia--</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Apartado</label>
                    <select class="form-select" v-model="idApartado" @change="cambiarApartado" :disabled="idMateria <= 0">
                        <option :value="0">--Selecciona un apartado--</option>
                        <option v-for="a in apartados" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Estado de la propuesta (dato propio de v4) -->
            <div class="row mb-3" v-if="idMateria > 0">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="terminadaSwitch" v-model="terminada" @change="guardarTerminada">
                        <label class="form-check-label" for="terminadaSwitch">Propuesta pedagógica terminada</label>
                    </div>
                    <small class="text-muted d-block">
                        Mientras la propuesta no esté terminada, no se podrá importar la programación de aula a partir de ella.
                    </small>
                </div>
            </div>

            <!-- Botones (misma barra que v3) -->
            <div class="row g-2 mb-3">
                <div class="col-md" v-if="esAdmin">
                    <button class="btn btn-light w-100" :disabled="idMateria <= 0" @click="irAContenidosDefecto" title="Contenidos por defecto de las unidades">
                        <i class="bi bi-database me-1"></i>Cont. defecto Unidades
                    </button>
                </div>

                <div class="col-md">
                    <button class="btn btn-light w-100" :disabled="idMateria <= 0" @click="irAUnidades" title="Gestionar las Unidades de Programación">
                        <i class="bi bi-list-ul me-1"></i>Unidades
                    </button>
                </div>

                <div class="col-md">
                    <button class="btn btn-light w-100" :disabled="idMateria <= 0" @click="generarPDFUnidades" title="PDF con todas las unidades de la materia">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF de Unidades
                    </button>
                </div>

                <div class="col-md">
                    <button class="btn btn-light w-100" :disabled="idApartado <= 0" @click="generarPDFApartado" title="PDF del apartado seleccionado">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF de Apartado
                    </button>
                </div>

                <div class="col-md">
                    <button class="btn btn-light w-100" :disabled="idMateria <= 0" @click="generarPDFCompleto" title="PDF completo de la programación">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF Completo
                    </button>
                </div>

                <div class="col-md" v-if="esAdmin">
                    <button class="btn btn-primary w-100" :disabled="idMateria <= 0" @click="mostrarImportar" title="Importar una programación desde otra materia">
                        <i class="bi bi-download me-1"></i>Importar
                    </button>
                </div>
            </div>

            <!-- Editor (apartado editable) -->
            <div v-if="esEditable" class="mt-3">
                <div class="card shadow-sm">
                    <div class="card-header text-bg-primary">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar apartado</h5>
                    </div>
                    <div class="card-body">
                        <textarea id="editorProgramacion"></textarea>
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
                    Aprendizaje. Para modificarlo, realiza los cambios en esas secciones. Puedes
                    visualizarlo haciendo clic en el botón "PDF de Apartado".</em>
                </div>
            </div>

            <!-- Importar programación -->
            <div class="modal fade" id="modalImportar" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-bg-primary">
                            <h5 class="modal-title"><i class="bi bi-download me-2"></i>Importar Programación</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Materia Origen *</label>
                                <select class="form-select" v-model="importarForm.idMateriaOrigen">
                                    <option value="">--Selecciona una materia origen--</option>
                                    <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                                </select>
                                <div class="form-text">Los datos de esta programación se copiarán a la materia destino.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Materia Destino *</label>
                                <select class="form-select" v-model="importarForm.idMateriaDestino">
                                    <option value="">--Selecciona una materia destino--</option>
                                    <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                                </select>
                                <div class="form-text">Esta materia recibirá los datos de la programación origen. ¡Se borrarán sus datos actuales!</div>
                            </div>
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Atención:</strong> Esta acción borrará todos los contenidos, temas y
                                criterios de evaluación de la materia destino antes de importar los nuevos datos.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="ejecutarImportar">
                                <i class="bi bi-check-lg me-1"></i>Importar
                            </button>
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
            apartados: [],
            idApartado: 0,
            tipoApartado: -1,
            contenido: '',
            guardando: false,
            terminada: false,
            modalImportar: null,
            importarForm: { idMateriaOrigen: '', idMateriaDestino: '' }
        };
    },

    computed: {
        esAdmin() {
            // Fiel a v3: $permisos = rol admin (controla Importar y Cont. defecto)
            return this.usuario.rol === 'admin';
        },
        esEditable() {
            return this.idMateria > 0 && this.idApartado > 0 && this.tipoApartado === 0;
        }
    },

    async mounted() {
        this.modalImportar = new bootstrap.Modal(document.getElementById('modalImportar'));
        await this.cargarMaterias();
    },

    beforeUnmount() {
        this.borrarEditor();
    },

    methods: {
        // --- TinyMCE (misma configuración que v3 / programaciones-aula) ---
        async inicializarEditor(texto) {
            if (!TinyMCEUtils.disponible()) {
                console.warn('TinyMCE no disponible — se muestra el textarea plano');
                return;
            }
            const ids = ['editorProgramacion'];
            await TinyMCEUtils.quitar(ids);
            const area = document.querySelector('textarea#editorProgramacion');
            if (!area) return;
            area.value = texto || '';
            await TinyMCEUtils.iniciar({
                selector: 'textarea#editorProgramacion',
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
            return TinyMCEUtils.quitar(['editorProgramacion']);
        },

        // --- Carga ---
        async cargarMaterias() {
            try {
                this.materias = await ProgramacionesAPI.cargarMaterias() || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async cambiarMateria() {
            await this.borrarEditor();
            this.idApartado = 0;
            this.tipoApartado = -1;
            this.apartados = [];
            if (this.idMateria <= 0) {
                this.terminada = false;
                return;
            }
            const materia = this.materias.find(m => m.id === this.idMateria);
            this.terminada = !!(materia && materia.terminada);

            try {
                this.apartados = await ProgramacionesAPI.cargarApartados(this.idMateria) || [];
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // --- Estado de la propuesta (v4 propia) ---
        // Marca la propuesta pedagógica de la materia como terminada o no.
        // Es lo que habilita importar la programación de aula.
        async guardarTerminada() {
            try {
                await ProgramacionesAPI.actualizarTerminada(this.idMateria, this.terminada ? 1 : 0);
                Avisos.exito('Éxito', this.terminada
                    ? 'La propuesta pedagógica está marcada como terminada'
                    : 'La propuesta pedagógica está marcada como no terminada');
            } catch (error) {
                // Revertir el interruptor si el guardado falla
                this.terminada = !this.terminada;
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
                    const data = await ProgramacionesAPI.cargarContenido(this.idMateria, this.idApartado);
                    this.contenido = (data && data.texto) || '';
                    this.$nextTick(() => {
                        this.inicializarEditor(this.contenido);
                    });
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        },

        // --- Guardar ---
        async guardar() {
            if (!this.esEditable) return;

            let texto = this.contenido;
            const editor = window.tinymce ? tinymce.get('editorProgramacion') : null;
            if (editor) {
                editor.save();
                texto = editor.getContent();
            }

            this.guardando = true;
            try {
                const res = await ProgramacionesAPI.guardarContenido(this.idMateria, this.idApartado, texto);
                if (res && res.sin_cambios) {
                    // Fiel a v3: si no hubo cambios, se avisa sin marcar error
                    Avisos.aviso('El contenido ya estaba guardado así (no se han realizado cambios).');
                } else {
                    Avisos.exito('Éxito', 'Contenido guardado correctamente');
                }
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.guardando = false;
            }
        },

        // --- Navegación a otras opciones (misma SPA) ---
        irAUnidades() {
            if (this.idMateria <= 0) return;
            // Enlaza a las unidades de la materia elegida (fiel a v3: temas.php?idMateria=X)
            this.$emit('navigate', 'temas.php', { idMateria: this.idMateria });
        },

        irAContenidosDefecto() {
            if (this.idMateria <= 0) return;
            this.$emit('navigate', 'temas_contenidos_defecto.php');
        },

        // --- PDFs (endpoints autocontenidos, sin sesión) ---
        generarPDFCompleto() {
            if (this.idMateria <= 0) return;
            window.open('../backend/pdf/pdf_programaciones.php?idMateria=' + this.idMateria, '_blank');
        },

        generarPDFApartado() {
            if (this.idApartado <= 0) return;
            // Si el apartado es de tipo TEMAS (13), el PDF "por apartado" es el de unidades
            if (this.tipoApartado === 13) {
                window.open('../backend/pdf/pdf_unidades_programacion.php?idMateria=' + this.idMateria, '_blank');
            } else {
                window.open('../backend/pdf/pdf_programaciones_apartado.php?idMateria=' + this.idMateria + '&idApartado=' + this.idApartado, '_blank');
            }
        },

        generarPDFUnidades() {
            if (this.idMateria <= 0) return;
            window.open('../backend/pdf/pdf_unidades_programacion.php?idMateria=' + this.idMateria, '_blank');
        },

        // --- Importar ---
        mostrarImportar() {
            this.importarForm = { idMateriaOrigen: '', idMateriaDestino: this.idMateria || '' };
            this.modalImportar.show();
        },

        async ejecutarImportar() {
            if (!this.importarForm.idMateriaOrigen || !this.importarForm.idMateriaDestino) {
                Avisos.error('Debe seleccionar ambas materias');
                return;
            }

            if (this.importarForm.idMateriaOrigen === this.importarForm.idMateriaDestino) {
                Avisos.error('Las materias origen y destino deben ser diferentes');
                return;
            }

            const result = await Swal.fire({
                title: '¿Confirmar importación?',
                html: '<p>Se borrarán todos los datos de la materia destino y se copiarán los de la materia origen.</p><p class="text-danger"><strong>¡Esta acción no se puede deshacer!</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, importar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await ProgramacionesAPI.importar(
                        this.importarForm.idMateriaOrigen,
                        this.importarForm.idMateriaDestino
                    );
                    Avisos.exito('Éxito', 'Programación importada correctamente');
                    this.modalImportar.hide();
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        }
    }
};
