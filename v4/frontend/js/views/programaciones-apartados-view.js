const ProgramacionesApartadosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-list-ul me-2"></i>Apartados de Programaciones</h2>
                        <button class="btn btn-primary" @click="nuevoApartado">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Apartado
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Arrastra cada apartado para ordenarlo. Haz clic en el lápiz para editar o en Nuevo para añadir.
                        Los apartados opcionales se indican con "(opcional)".
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div v-if="cargando" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            <div v-else-if="apartados.length === 0" class="text-center py-4 text-muted">
                                No hay apartados registrados
                            </div>
                            <div v-else class="list-group">
                                <div v-for="(apartado, index) in apartados" :key="apartado.id"
                                     :id="'ap' + apartado.id"
                                     class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                     :class="{'ms-4': apartado.subapartado}"
                                     draggable="true"
                                     @dragstart="dragStart($event, apartado)"
                                     @dragover.prevent
                                     @drop="drop($event, index)">
                                    <div>
                                        <strong>{{ getIndex(apartado) }}. {{ apartado.titulo }}</strong>
                                        <span v-if="!apartado.requerido" class="text-muted">(opcional)</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                @click="editarApartado(apartado)" 
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                @click="eliminarApartado(apartado)" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Formulario -->
            <div class="modal fade" id="modalApartado" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-bg-primary">
                            <h5 class="modal-title">{{ esNuevo ? 'Nuevo Apartado' : 'Editar Apartado' }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label class="form-label">Título *</label>
                                    <input type="text" class="form-control" v-model="formulario.titulo" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" v-model="formulario.subapartado">
                                    <label class="form-check-label">Es subapartado</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" v-model="formulario.requerido">
                                    <label class="form-check-label">Requerido (no opcional)</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" v-model="formulario.contenido_defecto">
                                    <label class="form-check-label">Tiene contenido por defecto</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Categoría *</label>
                                    <select class="form-select" v-model="formulario.categoria">
                                        <option value="" disabled>--Selecciona una categoría--</option>
                                        <option value="ESO/BACH">ESO / Bachillerato</option>
                                        <option value="FP">FP</option>
                                        <option value="TODOS">Todos</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <input type="number" class="form-control" v-model.number="formulario.tipo" min="0">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">
                                <i class="bi bi-save me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            apartados: [],
            cargando: false,
            formulario: {
                id: null,
                titulo: '',
                subapartado: false,
                requerido: false,
                contenido_defecto: false,
                categoria: '',
                tipo: 0
            },
            esNuevo: true,
            modal: null,
            draggedItem: null
        };
    },

    async mounted() {
        await this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalApartado'));
    },

    methods: {
        async cargar() {
            this.cargando = true;
            try {
                this.apartados = await ProgramacionesApartadosAPI.listar();
            } catch (error) {
                Avisos.error(error.message);
            } finally {
                this.cargando = false;
            }
        },

        getIndex(apartado) {
            // Numeración idéntica a v3: cont++ por apartado principal, cont2++ por subapartado dentro de su grupo
            let cont = 0;
            let cont2 = 0;
            for (let i = 0; i < this.apartados.length; i++) {
                const a = this.apartados[i];
                if (!a.subapartado) {
                    cont++;
                    cont2 = 0;
                } else {
                    cont2++;
                }
                if (a.id === apartado.id) {
                    return a.subapartado ? `${cont}.${cont2}` : `${cont}`;
                }
            }
            return '';
        },

        nuevoApartado() {
            this.esNuevo = true;
            this.formulario = {
                id: null,
                titulo: '',
                subapartado: false,
                requerido: false,
                contenido_defecto: false,
                categoria: '',
                tipo: 0
            };
            this.modal.show();
        },

        editarApartado(apartado) {
            this.esNuevo = false;
            this.formulario = {
                id: apartado.id,
                titulo: apartado.titulo,
                subapartado: !!apartado.subapartado,
                requerido: !!apartado.requerido,
                contenido_defecto: !!apartado.contenido_defecto,
                categoria: apartado.categoria || '',
                tipo: apartado.tipo || 0
            };
            this.modal.show();
        },

        async guardar() {
            if (!this.formulario.titulo.trim()) {
                Avisos.aviso('Debes indicar un título');
                return;
            }
            if (!this.formulario.categoria) {
                Avisos.aviso('Debes seleccionar una categoría');
                return;
            }

            const tipo = (this.formulario.tipo === null || this.formulario.tipo === undefined || this.formulario.tipo === '')
                ? 0
                : Math.max(0, parseInt(this.formulario.tipo));
            const apartadoGuardado = { ...this.formulario, tipo };

            try {
                await ProgramacionesApartadosAPI.guardar(apartadoGuardado);
                Avisos.exito('Éxito', 'Apartado guardado correctamente');
                this.modal.hide();
                await this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        async eliminarApartado(apartado) {
            const result = await Avisos.confirmar('¿Eliminar apartado?', `Confirmas el borrado del apartado '${apartado.titulo}'? Se eliminarán todos los contenidos de las programaciones relativos a dicho apartado.`, {
                icono: 'warning',
                boton: 'Sí, eliminar'
            });

            if (result.isConfirmed) {
                try {
                    await ProgramacionesApartadosAPI.eliminar(apartado.id);
                    Avisos.exito('Eliminada', 'Apartado eliminado correctamente');
                    await this.cargar();
                } catch (error) {
                    Avisos.error(error.message);
                }
            }
        },

        dragStart(event, apartado) {
            this.draggedItem = apartado;
            event.dataTransfer.effectAllowed = 'move';
        },

        async drop(event, targetIndex) {
            if (!this.draggedItem) return;
            
            const draggedIndex = this.apartados.findIndex(a => a.id === this.draggedItem.id);
            if (draggedIndex === targetIndex) return;

            // Reordenar array localmente
            const item = this.apartados.splice(draggedIndex, 1)[0];
            this.apartados.splice(targetIndex, 0, item);

            // Generar nuevo orden
            const orden = this.apartados.map(a => 'ap' + a.id).join(',');
            
            try {
                await ProgramacionesApartadosAPI.ordenar(orden);
                Swal.fire({
                    icon: 'success',
                    title: 'Orden actualizado',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            } catch (error) {
                Avisos.error(error.message);
                await this.cargar(); // Recargar para restaurar orden original
            }
            
            this.draggedItem = null;
        }
    }
};
