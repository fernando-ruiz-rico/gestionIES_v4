// Fase 6.1 — Actas de departamentos
// Fiel a v3: las actas de departamento se almacenan en actas_departamentos,
// una fila por acta (con su texto y fecha).
const ActasView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2><i class="bi bi-journal-text me-2"></i>Actas de departamentos</h2>
                    <p class="text-muted">
                        <em>Selecciona un departamento y una acta para revisarla, o crea una nueva con el botón <em>Nueva acta</em> (si eres jefe/a del departamento).</em>
                    </p>
                </div>
            </div>

            <!-- Selectores -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select class="form-select" v-model="idDepartamento" @change="cargar">
                        <option value="">-- Selecciona un departamento --</option>
                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Acta</label>
                    <select class="form-select" v-model="idActa" @change="cargarActa">
                        <option value="">-- Selecciona una acta --</option>
                        <option v-for="a in actas" :key="a.id" :value="a.id">{{ formatearFecha(a.fecha) }}</option>
                    </select>
                </div>
            </div>

            <!-- Área de edición -->
            <div v-if="idDepartamento" class="card shadow-sm">
                <div class="card-body">
                    <div class="row mb-3" v-if="permisos">
                        <div class="col-md-3">
                            <label class="form-label">Fecha reunión</label>
                            <input type="date" class="form-control" v-model="form.fecha">
                        </div>
                        <div class="col-md-5">
                            <button class="btn btn-outline-primary" @click="nuevaActa">
                                <i class="bi bi-plus-lg me-1"></i>Nueva acta
                            </button>
                        </div>
                    </div>
                    <div v-if="!idActa" class="text-center text-muted py-4">
                        Selecciona una acta para verla.
                    </div>
                    <div v-else v-html="actaContenido"></div>
                    <div v-if="permisos" class="text-center mt-3">
                        <button class="btn btn-primary" @click="guardar">
                            <i class="bi bi-save me-1"></i>Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            departamentos: [],
            idDepartamento: '',
            actas: [],
            idActa: '',
            actaContenido: '',
            permisos: false,
            form: { idActa: 0, fecha: '' }
        };
    },

    mounted() {
        this.cargarDepartamentos();
    },

    methods: {
        async cargarDepartamentos() {
            const result = await fetch('../backend/api/departamentos/listar.php', { credentials: 'same-origin' });
            this.departamentos = await result.json();
            // Si el usuario es admin, puede editar cualquier depto; si es profesor, no
            this.permisos = this.usuario && (this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento');
        },

        async cargar() {
            if (!this.idDepartamento) return;
            const res = await ActasAPI.listar(this.idDepartamento);
            if (res && res.success) this.actas = res.data;
        },

        async cargarActa() {
            if (!this.idActa) return;
            const res = await ActasAPI.obtener(this.idActa);
            if (res && res.success) {
                this.actaContenido = res.data.texto;
                this.form.fecha = res.data.fecha;
                this.form.idActa = this.idActa;
            }
        },

        formatearFecha(fecha) {
            if (!fecha) return '';
            const d = new Date(fecha);
            return d.toISOString().split('T')[0].replace(/-/g, '/');
        },

        nuevaActa() {
            const hoy = new Date().toISOString().split('T')[0].replace(/-/g, '/');
            Swal.fire({
                title: 'Nueva acta',
                text: '¿Quieres crear una acta nueva para la fecha actual?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            }).then(async res => {
                if (res.isConfirmed) {
                    const form = {
                        idActa: 0,
                        idDepartamento: this.idDepartamento,
                        texto: '<h3>Asistentes</h3><ol></ol><h3>Orden del día</h3><p>Por completar</p>',
                        fecha: hoy
                    };
                    const guardada = await ActasAPI.guardar(form);
                    if (guardada && guardada.success) {
                        Swal.fire({ icon: 'success', title: 'Acta creada', timer: 1000, showConfirmButton: false });
                        this.cargar();
                        // Seleccionamos la nueva acta
                        this.idActa = guardada.data.id;
                        this.cargarActa();
                    }
                }
            });
        },

        async guardar() {
            const form = {
                idActa: this.form.idActa,
                idDepartamento: this.idDepartamento,
                texto: this.actaContenido,
                fecha: this.form.fecha
            };
            const res = await ActasAPI.guardar(form);
            if (res && res.success) {
                Swal.fire({ icon: 'success', title: 'Acta guardada', timer: 1000, showConfirmButton: false });
            } else {
                Swal.fire('Error', res.error, 'error');
            }
        }
    }
};
