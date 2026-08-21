// Vista de gestión de los apartados del PCCF (Fase 3.2)
// Permite listar, crear, editar, ordenar y eliminar los apartados del PCCF
const PCCFApartadosView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-list me-2"></i>Apartados del PCCF</h2>
                    <button class="btn btn-primary" @click="nuevoApartado">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Apartado
                    </button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Arrastra cada apartado para ordenarlo. Haz clic en el lápiz para editar. Cada apartado puede
                        contener contenido para cada ciclo y contenido por defecto para cada departamento.
                    </div>
                </div>
            </div>

            <div v-if="cargando" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div v-else-if="apartados.length === 0" class="alert alert-warning">
                No hay apartados registrados
            </div>
            <div v-else class="list-group">
                <div v-for="(apartado, index) in apartados" :key="apartado.id"
                     :id="'ap' + apartado.id"
                     class="list-group-item d-flex justify-content-between align-items-center"
                     draggable="true"
                     @dragstart="dragStart(index)"
                     @click="cargarApartadoEditar(apartado)">
                    <span>
                        <i class="bi bi-grabme me-2"></i>{{ rule(apartado) }}
                    </span>
                    <i class="bi bi-pencil-square" style="cursor: pointer;"></i>
                </div>
            </div>

            <!-- Modal de creación / edición -->
            <div class="modal fade" id="modalApartado" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="modalApartadoTitle">Nuevo Apartado</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" v-model="formulario.titulo" required>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="subapartado" v-model="formulario.subapartado">
                                <label class="form-check-label" for="subapartado">¿Es subapartado?</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="requerido" v-model="formulario.requerido">
                                <label class="form-check-label" for="requerido">¿Requerido?</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="contenido_defecto" v-model="formulario.contenido_defecto">
                                <label class="form-check-label" for="contenido_defecto">¿Contenido por defecto?</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo</label>
                                <input type="number" class="form-control" v-model.number="formulario.tipo" min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="cerrarModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardarApartado">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            apartados: [],
            cargando: false,
            modal: null,
            draggedIndex: null,
            formulario: {
                id: null,
                titulo: '',
                subapartado: false,
                requerido: true,
                contenido_defecto: false,
                tipo: 0
            }
        };
    },

    mounted() {
        this.cargar();
        this.modal = new bootstrap.Modal(document.getElementById('modalApartado'));
    },

    methods: {
        rule(apartado) {
            // Numeración de apartados idéntica a v3 (cont++ / cont2++)
            let cont = 0;
            let cont2 = 0;
            for (let i = 0; i <= this.apartados.indexOf(apartado); i++) {
                const a = this.apartados[i];
                if (!a.subapartado) {
                    cont++;
                    cont2 = 0;
                } else {
                    cont2++;
                }
            }
            return !apartado.subapartado ? `${cont}.` : `${cont}.${cont2}.`;
        },

        async cargar() {
            this.cargando = true;
            try {
                this.apartados = await PCCFApartadosAPI.listar();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        nuevoApartado() {
            this.formulario = {
                id: null,
                titulo: '',
                subapartado: false,
                requerido: true,
                contenido_defecto: false,
                tipo: 0
            };
            this.modal.show();
        },

        cargarApartadoEditar(apartado) {
            this.formulario = {
                id: apartado.id,
                titulo: apartado.titulo,
                subapartado: !!apartado.subapartado,
                requerido: !!apartado.requerido,
                contenido_defecto: !!apartado.contenido_defecto,
                tipo: apartado.tipo
            };
            this.modal.show();
        },

        cerrarModal() {
            this.modal.hide();
        },

        async guardarApartado() {
            if (!this.formulario.titulo.trim()) {
                Swal.fire('Error', 'Debes indicar un título', 'warning');
                return;
            }
            try {
                await PCCFApartadosAPI.guardar(this.formulario);
                Swal.fire('Éxito', 'Apartado guardado correctamente', 'success');
                this.cerrarModal();
                await this.cargar();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        dragStart(index) {
            this.draggedIndex = index;
        }
    }
};
