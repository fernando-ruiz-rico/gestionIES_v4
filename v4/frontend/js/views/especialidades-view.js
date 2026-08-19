// Vista de Especialidades

const EspecialidadesView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-bookmark"></i> Especialidades</h2>
                        <button class="btn btn-primary" @click="abrirModalCrear()">
                            <i class="bi bi-plus-lg"></i> Nueva Especialidad
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="especialidad in especialidades" :key="especialidad.idEspecialidad">
                                            <td>{{ especialidad.idEspecialidad }}</td>
                                            <td>{{ especialidad.nombre }}</td>
                                            <td>{{ especialidad.descripcion || '-' }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        @click="editarEspecialidad(especialidad)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        @click="eliminarEspecialidad(especialidad)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="especialidades.length === 0">
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No hay especialidades registradas
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Crear/Editar -->
            <div class="modal fade" id="modalEspecialidad" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar Especialidad' : 'Nueva Especialidad' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardarEspecialidad">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" v-model="form.nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" v-model="form.descripcion" rows="3"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardarEspecialidad">
                                {{ esEdicion ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,
    
    data() {
        return {
            especialidades: [],
            form: {
                idEspecialidad: 0,
                nombre: '',
                descripcion: ''
            },
            esEdicion: false,
            modalInstance: null
        };
    },
    
    mounted() {
        this.cargarEspecialidades();
        this.modalInstance = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
    },
    
    methods: {
        async cargarEspecialidades() {
            this.especialidades = await EspecialidadesAPI.listar();
        },
        
        abrirModalCrear() {
            this.form = { idEspecialidad: 0, nombre: '', descripcion: '' };
            this.esEdicion = false;
            this.modalInstance.show();
        },
        
        editarEspecialidad(especialidad) {
            this.form = { ...especialidad };
            this.esEdicion = true;
            this.modalInstance.show();
        },
        
        async guardarEspecialidad() {
            const resultado = await EspecialidadesAPI.guardar(this.form);
            
            if (resultado.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: resultado.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.modalInstance.hide();
                this.cargarEspecialidades();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resultado.error || 'Error al guardar'
                });
            }
        },
        
        eliminarEspecialidad(especialidad) {
            Swal.fire({
                title: '¿Eliminar especialidad?',
                text: `¿Estás seguro de eliminar "${especialidad.nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const resultado = await EspecialidadesAPI.eliminar(especialidad.idEspecialidad);
                    
                    if (resultado.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminada',
                            text: resultado.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        this.cargarEspecialidades();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resultado.error || 'Error al eliminar'
                        });
                    }
                }
            });
        }
    }
};
