// Vista de Escenarios

const EscenariosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-building"></i> Escenarios</h2>
                    <button class="btn btn-primary" @click="abrirModal()">
                        <i class="bi bi-plus-lg"></i> Nuevo Escenario
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
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="e in escenarios" :key="e.id">
                                        <td>{{ e.id }}</td>
                                        <td>{{ e.nombre }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(e)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(e)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!escenarios.length">
                                        <td colspan="3" class="text-center text-muted py-4">Sin escenarios</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalEscenario" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} Escenario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" v-model="form.nombre" required>
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
            escenarios: [],
            form: { id: 0, nombre: '' },
            esEdicion: false,
            modal: null
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalEscenario'));
    },

    methods: {
        async cargar() {
            const result = await EscenariosAPI.listar();
            if (result.success) {
                this.escenarios = result.data;
            } else {
                this.escenarios = [];
            }
        },

        abrirModal() {
            this.form = { id: 0, nombre: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(escenario) {
            this.form = { ...escenario };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await EscenariosAPI.guardar(this.form);

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

        eliminar(escenario) {
            Swal.fire({
                title: '¿Eliminar escenario?',
                text: escenario.nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await EscenariosAPI.eliminar(escenario.id);

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
