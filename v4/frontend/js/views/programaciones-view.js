// FASE 2.1 — Programaciones Didácticas (modelo fiel a v3)
// No existe una "fila de programación": cada materia guarda su programación como
// apartados + contenidos. Aquí solo se listan las materias con programación activa,
// se ve su estado real y se permite importarla. La edición está en las fases 2.2-2.5.
const ProgramacionesView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-journal-bookmark me-2"></i>Programaciones Didácticas</h2>
                        <button class="btn btn-outline-primary" @click="mostrarImportar">
                            <i class="bi bi-download me-1"></i>Importar Programación
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtro por materia -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Materia</label>
                    <select class="form-select" v-model="filtroMateria" @change="cargar">
                        <option value="">Todas</option>
                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de programaciones (estado real por materia) -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Materia</th>
                                            <th>Curso</th>
                                            <th class="text-center">Horas</th>
                                            <th class="text-center">Apartados</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="cargando">
                                            <td colspan="5" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else-if="programaciones.length === 0">
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No hay materias con programación activa
                                            </td>
                                        </tr>
                                        <tr v-else v-for="prog in programaciones" :key="prog.id">
                                            <td>{{ prog.materia }}</td>
                                            <td>{{ prog.curso || '—' }}</td>
                                            <td class="text-center">{{ horasFormateadas(prog) }}</td>
                                            <td class="text-center">
                                                <span v-if="prog.num_apartados > 0" class="badge text-bg-success">
                                                    {{ prog.num_apartados }}
                                                </span>
                                                <span v-else class="text-muted">Sin apartados</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary" @click="ver(prog)" title="Ver programación">
                                                    <i class="bi bi-eye me-1"></i>Ver
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

            <!-- Modal Ver programación (solo lectura) -->
            <div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Programación — {{ verData.materia }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p v-if="verData.apartados.length === 0" class="text-muted mb-0">
                                Esta materia no tiene apartados ni contenidos cargados.
                            </p>
                            <div v-else v-for="(ap, i) in verData.apartados" :key="i" class="border rounded p-3 mb-2">
                                <h6 class="mb-1"><i class="bi bi-list-ol me-1"></i>{{ ap.titulo }}</h6>
                                <div v-html="ap.texto"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Importar Programación -->
            <div class="modal fade" id="modalImportar" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-download me-2"></i>Importar Programación</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="ejecutarImportar">
                                <div class="mb-3">
                                    <label class="form-label">Materia Origen *</label>
                                    <select class="form-select" v-model="importarForm.idMateriaOrigen" required>
                                        <option value="">--Selecciona una materia origen--</option>
                                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                                    </select>
                                    <div class="form-text">Los datos de esta programación se copiarán a la materia destino.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Materia Destino *</label>
                                    <select class="form-select" v-model="importarForm.idMateriaDestino" required>
                                        <option value="">--Selecciona una materia destino--</option>
                                        <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                                    </select>
                                    <div class="form-text">Esta materia recibirá los datos de la programación origen. ¡Se borrarán sus datos actuales!</div>
                                </div>
                                <div class="alert alert-warning" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Atención:</strong> Esta acción borrará todos los contenidos, temas y criterios de evaluación de la materia destino antes de importar los nuevos datos.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="ejecutarImportar">
                                <i class="bi bi-check-lg me-1"></i>Importar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            programaciones: [],
            materias: [],
            cargando: false,
            filtroMateria: '',
            importarForm: { idMateriaOrigen: '', idMateriaDestino: '' },
            verData: { materia: '', apartados: [] },
            modalVer: null,
            modalImportar: null
        };
    },

    async mounted() {
        await this.cargar();
        await this.cargarCatalogos();
        this.modalVer = new bootstrap.Modal(document.getElementById('modalVer'));
        this.modalImportar = new bootstrap.Modal(document.getElementById('modalImportar'));
    },

    methods: {
        horasFormateadas(prog) {
            return (prog.horas !== null && prog.horas !== undefined && prog.horas !== '') ? prog.horas : '—';
        },

        async cargar() {
            this.cargando = true;
            try {
                this.programaciones = await programacionesAPI.listar(this.filtroMateria || null);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            } finally {
                this.cargando = false;
            }
        },

        async cargarCatalogos() {
            try {
                // listar.php devuelve el array de materias directamente
                const res = await fetch('../backend/api/materias/listar.php').then(r => r.json());
                if (Array.isArray(res)) this.materias = res;
            } catch (error) {
                console.error('Error al cargar catálogos:', error);
            }
        },

        async ver(prog) {
            try {
                const data = await programacionesAPI.obtener(prog.id);
                this.verData = { materia: prog.materia, apartados: data || [] };
                this.modalVer.show();
            } catch (error) {
                Swal.fire({
                    icon: 'info',
                    title: 'Programación vacía',
                    text: 'Esta materia aún no tiene apartados ni contenidos cargados. Se editan en las fases 2.2 y 2.4.'
                });
            }
        },

        mostrarImportar() {
            this.importarForm = { idMateriaOrigen: '', idMateriaDestino: '' };
            this.modalImportar.show();
        },

        async ejecutarImportar() {
            if (!this.importarForm.idMateriaOrigen || !this.importarForm.idMateriaDestino) {
                Swal.fire('Error', 'Debe seleccionar ambas materias', 'error');
                return;
            }

            if (this.importarForm.idMateriaOrigen === this.importarForm.idMateriaDestino) {
                Swal.fire('Error', 'Las materias origen y destino deben ser diferentes', 'error');
                return;
            }

            const result = await Swal.fire({
                title: '¿Confirmar importación?',
                html: '<p>Se borrarán todos los datos de la materia destino y se copiarán los de la materia origen.</p><p class="text-danger"><strong>¡Esta acción no se puede deshacer!</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, importar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await programacionesAPI.importar(
                        this.importarForm.idMateriaOrigen,
                        this.importarForm.idMateriaDestino
                    );
                    Swal.fire('Éxito', 'Programación importada correctamente', 'success');
                    this.modalImportar.hide();
                    await this.cargar();
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            }
        }
    }
};
