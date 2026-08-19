// Vista de Especialidades

const EspecialidadesView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-bookmark"></i> Especialidades</h2>
                    <button class="btn btn-primary" @click="abrirModalCrear()">
                        <i class="bi bi-plus-lg"></i> Nueva Especialidad
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body table-responsive">
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
                                    <tr v-for="e in especialidades" :key="e.idEspecialidad">
                                        <td>{{ e.idEspecialidad }}</td>
                                        <td>{{ e.nombre }}</td>
                                        <td>{{ e.descripcion || '-' }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(e)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(e)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!especialidades.length">
                                        <td colspan="4" class="text-center text-muted py-4">Sin especialidades</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalEspecialidad" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nueva' }} Especialidad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
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
                            <button type="button" class="btn btn-primary" @click="guardar">
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
            form: { idEspecialidad: 0, nombre: '', descripcion: '' },
            esEdicion: false,
            modal: null
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
    },

    methods: {
        async cargar() {
            const result = await EspecialidadesAPI.listar();
            if (result.success) {
                this.especialidades = result.data;
            } else {
                this.especialidades = [];
            }
        },

        abrirModalCrear() {
            this.form = { idEspecialidad: 0, nombre: '', descripcion: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(especialidad) {
            this.form = { ...especialidad };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await EspecialidadesAPI.guardar(this.form);

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                this.modal.hide();
                this.cargar();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error
                });
            }
        },

        eliminar(especialidad) {
            Swal.fire({
                title: '¿Eliminar especialidad?',
                text: especialidad.nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await EspecialidadesAPI.eliminar(especialidad.idEspecialidad);

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        this.cargar();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.error
                        });
                    }
                }
            });
        }
    }
};
