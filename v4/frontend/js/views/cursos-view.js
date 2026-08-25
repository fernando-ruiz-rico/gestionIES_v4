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
                                        <th>Abreviatura</th>
                                        <th>Horas semanales</th>
                                        <th>Categoría</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in cursos" :key="c.id">
                                        <td>{{ c.id }}</td>
                                        <td>{{ c.nombre }}</td>
                                        <td>{{ c.abreviatura }}</td>
                                        <td>{{ c.horas_semana || 0 }}</td>
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
                                        <td colspan="6" class="text-center text-muted py-4">Sin cursos</td>
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
                                    <label class="form-label">Abreviatura *</label>
                                    <input type="text" class="form-control" v-model="form.abreviatura" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Horas de clase semanales</label>
                                    <input type="number" min="0" class="form-control" v-model="form.horas_semana"
                                           placeholder="Deja el campo vacío o pon 0 si el curso no debe sumar X horas semanales">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" v-model="form.categoria">
                                        <option value="">--Selecciona una categoría--</option>
                                        <option value="ESO">ESO</option>
                                        <option value="BACH">Bachillerato</option>
                                        <option value="FP">FP</option>
                                        <option value="OTROS">Otros</option>
                                    </select>
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
            form: { id: 0, nombre: '', abreviatura: '', horas_semana: '', categoria: '' },
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
            try {
                this.cursos = await CursosAPI.listar() || [];
            } catch (error) {
                this.cursos = [];
            }
        },

        abrirModal() {
            this.form = { id: 0, nombre: '', abreviatura: '', horas_semana: '', categoria: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(curso) {
            this.form = {
                id: curso.id,
                nombre: curso.nombre,
                abreviatura: curso.abreviatura,
                horas_semana: curso.horas_semana,
                categoria: curso.categoria
            };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            try {
                const result = await CursosAPI.guardar(this.form);
                Avisos.exito('Éxito', result.message);
                this.modal.hide();
                this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        eliminar(curso) {
            Avisos.confirmar('¿Eliminar curso?', curso.nombre).then(async (res) => {
                if (res.isConfirmed) {
                    try {
                        await CursosAPI.eliminar(curso.id);
                        Avisos.exito();
                        this.cargar();
                    } catch (error) {
                        Avisos.error(error.message);
                    }
                }
            });
        }
    }
};
