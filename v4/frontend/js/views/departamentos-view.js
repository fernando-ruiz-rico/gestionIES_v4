// Componente Departamentos View (gestión de departamentos)
//
// Autocontenido: el listado, el modal de alta/edición y las acciones (nuevo,
// editar, borrar, guardar) viven aquí como datos y métodos del componente,
// igual que el resto de vistas (antes usaban funciones globales en
// js/departamentos.js, que se fusiona en este archivo).
const DepartamentosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-3">
                        <i class="bi bi-building me-2"></i>Departamentos
                    </h1>
                    <p class="text-muted">
                        <em>Haz clic en el icono del lápiz para editar los datos de cada departamento, y en el botón de Nuevo al final para añadir nuevos. Puedes eliminar departamentos con el icono de borrar junto a cada uno. En este caso, sólo se borrará el departamento si no tiene profesores vinculados al mismo (deberás borrarlos antes).</em>
                    </p>
                </div>
            </div>

            <!-- Listado de departamentos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 h6"><i class="bi bi-list-ul me-2"></i>Listado de departamentos</h5>
                </div>
                <div class="card-body p-0">
                    <div v-if="cargando" class="text-center text-muted py-3">Cargando…</div>
                    <div v-else-if="departamentos.length === 0" class="alert alert-info m-3">No hay departamentos registrados</div>
                    <div v-else class="list-group list-group-flush">
                        <div v-for="d in departamentos" :key="d.id"
                             class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building me-3 text-primary fs-5"></i>
                                <span class="fw-medium">{{ d.nombre }}</span>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary btn-sm" title="Editar" @click="editar(d)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" title="Eliminar" @click="borrar(d)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para abrir el diálogo modal para crear nuevos departamentos -->
            <div class="text-center">
                <button class="btn btn-primary" @click="nuevo()">
                    <i class="bi bi-plus-lg me-2"></i>Nuevo Departamento
                </button>
            </div>

            <!-- Diálogo modal para crear/editar departamentos -->
            <div id="formdepartamento" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-building me-2"></i>Formulario de departamentos
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <!-- input oculto con el id (0 = alta) -->
                            <input type="hidden" v-model="form.id">
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="nombre">Nombre del departamento</label>
                                <input class="form-control" type="text" id="nombre" v-model="form.nombre" required placeholder="Ej: Departamento de Matemáticas">
                            </div>
                        </div>
                        <div class="modal-footer d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i>Cancelar
                            </button>
                            <button class="btn btn-primary" @click="guardar">
                                <i class="bi bi-check-lg me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            departamentos: [],
            cargando: true,
            form: { id: 0, nombre: '' },
            modal: null
        };
    },

    mounted() {
        // Instancia del modal de Bootstrap para mostrarlo/ocultarlo desde JS
        this.modal = new bootstrap.Modal(document.getElementById('formdepartamento'));
        this.cargar();
    },

    methods: {
        // Carga el listado de departamentos
        async cargar() {
            this.cargando = true;
            try {
                this.departamentos = await DepartamentosAPI.listar() || [];
            } catch (error) {
                console.error('Error al cargar departamentos:', error);
                Avisos.error('Error al cargar los departamentos');
                this.departamentos = [];
            } finally {
                this.cargando = false;
            }
        },

        // Abre el modal en modo alta (formulario vacío)
        nuevo() {
            this.form = { id: 0, nombre: '' };
            this.modal.show();
        },

        // Carga un departamento en el formulario y abre el modal en modo edición
        async editar(depto) {
            try {
                const d = await DepartamentosAPI.obtener(depto.id) || {};
                this.form = { id: d.id || 0, nombre: d.nombre || '' };
                this.modal.show();
            } catch (error) {
                console.error('Error al cargar departamento:', error);
                Avisos.error('Error al cargar el departamento');
            }
        },

        // Guarda el formulario (alta o edición)
        async guardar() {
            try {
                const data = await DepartamentosAPI.guardar({
                    idDepartamento: this.form.id,
                    nombre: this.form.nombre
                });
                Avisos.exito('¡Éxito!', data.message || 'Departamento guardado correctamente');
                this.modal.hide();
                this.cargar();
            } catch (error) {
                console.error('Error al guardar departamento:', error);
                Avisos.error(error.message || 'Error al guardar el departamento');
            }
        },

        // Borra un departamento, previa confirmación
        borrar(depto) {
            Avisos.confirmar(
                '¿Confirmar borrado?',
                "¿Estás seguro de eliminar el departamento '" + depto.nombre + "'? Sólo se podrá eliminar si no tiene profesores u otros recursos asociados.",
                {
                    boton: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
                    cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }
            ).then(async (result) => {
                if (!result.isConfirmed) return;
                try {
                    await DepartamentosAPI.eliminar(depto.id);
                    Avisos.exito('¡Éxito!', 'Departamento eliminado correctamente');
                    this.cargar();
                } catch (error) {
                    console.error('Error al eliminar departamento:', error);
                    Avisos.error(error.message || 'Error al eliminar el departamento');
                }
            });
        }
    }
};
