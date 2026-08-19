const pccf-apartados-viewView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="bi bi-file-text me-2"></i>Apartados PCCF</h2>
                        <button class="btn btn-primary" @click="abrirModal()">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div v-if="cargando" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            <div v-else-if="error" class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ error }}
                            </div>
                            <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-3">No hay elementos registrados</p>
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Título/Nombre</th>
                                            <th>Orden</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in items" :key="item.id">
                                            <td>{{ item.id }}</td>
                                            <td>{{ item.titulo || item.nombre || '-' }}</td>
                                            <td>{{ item.orden || '-' }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1" @click="editar(item)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" @click="eliminar(item)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal -->
            <div v-if="modalAbierto" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ editando ? 'Editar' : 'Nuevo' }} Apartados PCCF</h5>
                            <button type="button" class="btn-close" @click="cerrarModal()"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Título</label>
                                    <input type="text" class="form-control" v-model="formulario.titulo" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contenido</label>
                                    <textarea class="form-control" v-model="formulario.contenido" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Orden</label>
                                    <input type="number" class="form-control" v-model.number="formulario.orden">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="modalAbierto" class="modal-backdrop fade show"></div>
        </div>
    `,
    
    data() {
        return {
            items: [],
            cargando: true,
            error: null,
            modalAbierto: false,
            editando: false,
            formulario: { id: null, titulo: '', contenido: '', orden: 0 }
        };
    },
    
    mounted() {
        this.cargar();
    },
    
    methods: {
        cargar() {
            this.cargando = true;
            this.error = null;
            pccfApartados.listar()
                .then(response => {
                    if (response.success) {
                        this.items = response.data || [];
                    } else {
                        this.error = response.error || 'Error al cargar datos';
                    }
                })
                .catch(err => {
                    this.error = 'Error de conexión: ' + err.message;
                    console.error(err);
                })
                .finally(() => {
                    this.cargando = false;
                });
        },
        
        abrirModal() {
            this.editando = false;
            this.formulario = { id: null, titulo: '', contenido: '', orden: 0 };
            this.modalAbierto = true;
        },
        
        editar(item) {
            this.editando = true;
            this.formulario = { ...item };
            this.modalAbierto = true;
        },
        
        cerrarModal() {
            this.modalAbierto = false;
        },
        
        guardar() {
            pccfApartados.guardar(this.formulario)
                .then(response => {
                    if (response.success) {
                        Swal.fire('Éxito', response.message || 'Guardado correctamente', 'success');
                        this.cerrarModal();
                        this.cargar();
                    } else {
                        Swal.fire('Error', response.error || 'Error al guardar', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Error de conexión: ' + err.message, 'error');
                });
        },
        
        eliminar(item) {
            Swal.fire({
                title: '¿Eliminar?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    pccfApartados.eliminar(item.id)
                        .then(response => {
                            if (response.success) {
                                Swal.fire('Eliminado', 'Elemento eliminado correctamente', 'success');
                                this.cargar();
                            } else {
                                Swal.fire('Error', response.error || 'Error al eliminar', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Error de conexión: ' + err.message, 'error');
                        });
                }
            });
        }
    }
};
