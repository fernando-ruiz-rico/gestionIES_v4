// Vista de Grupos

const GruposView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-people"></i> Grupos</h2>
                    <button class="btn btn-primary" @click="abrirModal()">
                        <i class="bi bi-plus-lg"></i> Nuevo Grupo
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
                                    <tr v-for="g in grupos" :key="g.idGrupo">
                                        <td>{{ g.idGrupo }}</td>
                                        <td>{{ g.nombre }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(g)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(g)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!grupos.length">
                                        <td colspan="3" class="text-center text-muted py-4">Sin grupos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalGrupo" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} Grupo</h5>
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
            grupos: [],
            form: { idGrupo: 0, nombre: '' },
            esEdicion: false,
            modal: null
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalGrupo'));
    },

    methods: {
        async cargar() {
            const result = await GruposAPI.listar();
            if (result.success) {
                this.grupos = result.data;
            } else {
                this.grupos = [];
            }
        },

        abrirModal() {
            this.form = { idGrupo: 0, nombre: '' };
            this.esEdicion = false;
            this.modal.show();
        },

        editar(grupo) {
            this.form = { ...grupo };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await GruposAPI.guardar(this.form);

            if (result.success) {
                Avisos.exito('Éxito', result.message);

                this.modal.hide();
                this.cargar();
            } else {
                Avisos.error(result.error);
            }
        },

        eliminar(grupo) {
            Avisos.confirmar('¿Eliminar grupo?', grupo.nombre).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await GruposAPI.eliminar(grupo.idGrupo);

                    if (result.success) {
                        Avisos.exito();
                        this.cargar();
                    } else {
                        Avisos.error(result.error);
                    }
                }
            });
        }
    }
};
