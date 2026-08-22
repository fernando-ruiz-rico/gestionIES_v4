// Componente Departamentos View (gestión de departamentos)
const DepartamentosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-3">
                        <i class="bi bi-building me-2"></i>Departamentos
                    </h1>
                    <p class="text-muted">
                        <em>Haz clic en el icono del lápiz para editar los datos de cada departamento, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar departamentos con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el departamento si no tiene profesores vinculados al mismo (deberás borrarlos antes).</em>
                    </p>
                </div>
            </div>
            
            <!-- Contenedor de mensajes -->
            <div id="mensajes" class="mb-3"></div>
            
            <!-- Listado de departamentos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 h6"><i class="bi bi-list-ul me-2"></i>Listado de departamentos</h5>
                </div>
                <div class="card-body p-0">
                    <div id="listadepartamentos" class="list-group list-group-flush">
                        <!-- Se carga por AJAX -->
                    </div>
                </div>
            </div>
            
            <!-- Botón para abrir el diálogo modal para crear nuevos departamentos -->
            <div class="text-center">
                <button class="btn btn-primary" onclick="nuevoDepartamento()">
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
                            <form id="formdep" name="formdep" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" id="idDepartamento" value="">
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="nombre">Nombre del departamento</label>
                                    <input class="form-control" type="text" name="nombre" id="nombre" required placeholder="Ej: Departamento de Matemáticas">
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-secondary me-md-2" type="button" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg me-1"></i>Cancelar
                                    </button>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-check-lg me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,
    
    data() {
        return {
            // Datos locales si son necesarios
        };
    },
    
    methods: {
        // Métodos si son necesarios
    },
    
    mounted() {
        // Cargar el listado de departamentos al montar el componente
        this.$nextTick(() => {
            cargarDepartamentos();
        });
    }
};
