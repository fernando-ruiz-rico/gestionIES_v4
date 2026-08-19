// Vista de Cursos

const CursosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-journal-bookmark"></i> Cursos</h2>
                    <button class="btn btn-primary" @click="abrirModal()">
                        <i class="bi bi-plus-lg"></i> Nuevo Curso
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
                                        <th>Categoría</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in cursos" :key="c.idCurso">
                                        <td>{{ c.idCurso }}</td>
                                        <td>{{ c.nombre }}</td>
                                        <td>{{ c.categoria || '-' }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(c)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!cursos.length">
                                        <td colspan="4" class="text-center text-muted py-4">Sin cursos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalCurso" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} Curso</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" v-model="form.nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Categoría</label>
                                    <input type="text" class="form-control" v-model="form.categoria">
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
            cursos: [],
            form: { idCurso: 0, nombre: '', categoria: '' },
            esEdicion: false,
            modal: null
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalCurso'));
    },

    methods: {
        async cargar() {
            const result = await CursosAPI.listar();
            if (result.success) {
                this.cursos = result.data;
            } else {
                this.cursos = [];
            }
        },

        abrirModal() {
            this.form = { idCurso: 0, nombre: '', categoria: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(curso) {
            this.form = { ...curso };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await CursosAPI.guardar(this.form);

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

        eliminar(curso) {
            Swal.fire({
                title: '¿Eliminar curso?',
                text: curso.nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await CursosAPI.eliminar(curso.idCurso);

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
