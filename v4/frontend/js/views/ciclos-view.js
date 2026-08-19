const CiclosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-layers"></i> Ciclos Formativos</h2>
                    <button class="btn btn-primary" @click="abrirModalCrear()"><i class="bi bi-plus-lg"></i> Nuevo Ciclo</button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>ID</th><th>Nombre</th><th>Especialidad</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody>
                                    <tr v-for="c in ciclos" :key="c.idCiclo">
                                        <td>{{ c.idCiclo }}</td><td>{{ c.nombre }}</td><td>{{ c.especialidad || '-' }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="editar(c)"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" @click="eliminar(c)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="!ciclos.length"><td colspan="4" class="text-center text-muted py-4">Sin ciclos</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalCiclo" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nuevo' }} Ciclo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <form @submit.prevent="guardar">
                            <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" class="form-control" v-model="form.nombre" required></div>
                            <div class="mb-3"><label class="form-label">Especialidad</label>
                                <select class="form-select" v-model="form.idEspecialidad">
                                    <option :value="0">Seleccionar...</option>
                                    <option v-for="e in especialidades" :key="e.idEspecialidad" :value="e.idEspecialidad">{{ e.nombre }}</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" @click="guardar">{{ esEdicion ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </div></div>
            </div>
        </div>`,
    data() { return { ciclos: [], especialidades: [], form: { idCiclo: 0, nombre: '', idEspecialidad: 0 }, esEdicion: false, modal: null }; },
    mounted() { this.cargar(); this.modal = new bootstrap.Modal(document.getElementById('modalCiclo')); EspecialidadesAPI.listar().then(d => this.especialidades = d); },
    methods: {
        async cargar() { this.ciclos = await CiclosAPI.listar(); },
        abrirModalCrear() { this.form = { idCiclo: 0, nombre: '', idEspecialidad: 0 }; this.esEdicion = false; this.modal.show(); },
        editar(c) { this.form = { ...c }; this.esEdicion = true; this.modal.show(); },
        async guardar() { const r = await CiclosAPI.guardar(this.form); if (r.success) { Swal.fire({ icon: 'success', title: 'Éxito', text: r.message, timer: 1500, showConfirmButton: false }); this.modal.hide(); this.cargar(); } else { Swal.fire({ icon: 'error', title: 'Error', text: r.error }); } },
        eliminar(c) { Swal.fire({ title: '¿Eliminar?', text: c.nombre, icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí' }).then(async res => { if (res.isConfirmed) { const r = await CiclosAPI.eliminar(c.idCiclo); if (r.success) { Swal.fire({ icon: 'success', timer: 1500 }); this.cargar(); } else { Swal.fire({ icon: 'error', text: r.error }); } } }); }
    }
};
