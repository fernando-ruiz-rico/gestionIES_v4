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
                        Arrastra cada apartado para ordenarlo respecto al resto. Haz clic en el icono del lápiz
                        para editar los datos de cada apartado, y en el de la papelera para borrarlo. Cada apartado
                        puede contener contenido para cada ciclo y contenido por defecto para cada departamento.
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
                     @dragover.prevent
                     @drop.prevent="drop(index)">
                    <span class="me-2 text-truncate" style="min-width: 0; max-width: 78%;">
                        <i class="bi bi-grip-vertical me-2 text-muted"></i>
                        <strong>{{ rule(apartado) }}</strong>
                        {{ apartado.titulo }}{{ opcionalText(apartado) }}
                    </span>
                    <div class="d-flex gap-2 text-nowrap">
                        <button class="btn btn-light" type="button" title="Borrar el apartado" @click="borrarApartado(apartado)">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                        <button class="btn btn-light" type="button" title="Editar el apartado" @click="cargarApartadoEditar(apartado)">
                            <i class="bi bi-pencil text-primary"></i>
                        </button>
                    </div>
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
            // Los campos llegan como cadenas ("0"/"1") desde la API: hay que
            // compararlos numéricamente, no con un simple "!" (en JS "0" es
            // "truthy", al contrario que en PHP).
            let cont = 0;
            let cont2 = 0;
            for (let i = 0; i <= this.apartados.indexOf(apartado); i++) {
                const a = this.apartados[i];
                if (Number(a.subapartado) === 0) {
                    cont++;
                    cont2 = 0;
                } else {
                    cont2++;
                }
            }
            return Number(apartado.subapartado) === 0 ? `${cont}.` : `${cont}.${cont2}.`;
        },

        opcionalText(apartado) {
            // Igual que v3: los no requeridos se señalan como "(opcional)"
            return Number(apartado.requerido) === 1 ? '' : ' (opcional)';
        },

        async cargar() {
            this.cargando = true;
            try {
                this.apartados = await PCCFApartadosAPI.listar();
            } catch (error) {
                Avisos.error(error.message);
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
                subapartado: Number(apartado.subapartado) === 1,
                requerido: Number(apartado.requerido) === 1,
                contenido_defecto: Number(apartado.contenido_defecto) === 1,
                tipo: Number(apartado.tipo)
            };
            this.modal.show();
        },

        // Borra un apartado, previa confirmación (fiel a v3)
        async borrarApartado(apartado) {
            const result = await Avisos.confirmar('¿Borrar este apartado?', `Se eliminará el apartado "${apartado.titulo}" y todos sus contenidos.`, {
                icono: 'warning',
                boton: 'Sí, borrar',
                confirmButtonColor: '#d81b60',
                reverseButtons: true
            });
            if (!result.isConfirmed) {
                return;
            }
            try {
                await PCCFApartadosAPI.eliminar(apartado.id);
                Avisos.exito('Éxito', 'Apartado eliminado correctamente');
                await this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        cerrarModal() {
            this.modal.hide();
        },

        async guardarApartado() {
            if (!this.formulario.titulo.trim()) {
                Avisos.aviso('Debes indicar un título');
                return;
            }
            try {
                await PCCFApartadosAPI.guardar(this.formulario);
                Avisos.exito('Éxito', 'Apartado guardado correctamente');
                this.cerrarModal();
                await this.cargar();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // --- Reordenación por arrastre (fiel al "sortable" de v3) ---
        dragStart(index) {
            this.draggedIndex = index;
        },

        drop(index) {
            const from = this.draggedIndex;
            this.draggedIndex = null;
            if (from === null || from === index) {
                return;
            }
            // Mueve el apartado arrastrado a la posición del destino
            // (misma sensación que el "sortable" de v3).
            const items = this.apartados;
            const [movido] = items.splice(from, 1);
            items.splice(index, 0, movido);
            this.guardarOrden();
        },

        async guardarOrden() {
            // Los ids de las filas llevan el prefijo "ap" (p. ej. ap1, ap12);
            // el endpoint los entiende con o sin el prefijo.
            const orden = this.apartados.map(a => 'ap' + a.id).join(',');
            try {
                await PCCFApartadosAPI.ordenar(orden);
                await this.cargar();
            } catch (error) {
                Avisos.error(error.message);
                await this.cargar();
            }
        }
    }
};
