// Componente Departamentos View (gestión de departamentos)
const DepartamentosView = {
    template: `
        <section class="departamentos">
            <div class="panelcentral">
                <h1>Departamentos</h1>
                <p><em>Haz clic en el icono del lápiz para editar los datos de cada departamento, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar departamentos con el icono de borrar junto a cada apartado. En este caso, sólo se borrará el departamento si no tiene profesores vinculados al mismo (deberás borrarlos antes).</em></p>
                
                <!-- Contenedor de mensajes -->
                <div id="mensajes"></div>
                
                <!-- En este div se carga por AJAX el listado de departamentos -->
                <div id="listadepartamentos"></div>
                
                <!-- Botón para abrir el diálogo modal para crear nuevos departamentos -->
                <div style="text-align: center" class="mt-4">
                    <button class="btn btn-light" onclick="nuevoDepartamento()">
                        <img src="img/add.png" alt="Nuevo" /> Nuevo Departamento
                    </button>
                </div>
            </div>
            
            <!-- Diálogo modal para crear/editar departamentos -->
            <div id="formdepartamento" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Formulario de alta/edición de departamentos</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formdep" name="formdep" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" id="idDepartamento" value="">
                                <div class="mb-3">
                                    <label class="form-label" for="nombre">Nombre del departamento</label>
                                    <input class="form-control" type="text" name="nombre" id="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Enviar</button>
                                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
