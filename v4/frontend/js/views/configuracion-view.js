// Fase 7.3 — Configuración
// Fiel a v3: el usuario puede cambiar su contraseña (nueva y confirmación), y un
// admin puede activar/desactivar la evaluación de RA y la activación de la
// selección de materias.
const ConfiguracionView = {
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
                    <h2><i class="bi bi-gear me-2"></i>Configuración</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><h5 class="h6 mb-0">Cambiar contraseña</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Contraseña actual</label>
                                <input type="password" class="form-control" v-model="passwordForm.passwordActual">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" v-model="passwordForm.nuevaPassword">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control" v-model="passwordForm.passwordConfirmacion">
                            </div>
                            <button class="btn btn-primary" @click="guardarPassword">
                                <i class="bi bi-save me-1"></i>Guardar contraseña
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" v-if="esAdmin">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><h5 class="h6 mb-0">Ajustes de aplicación</h5></div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" class="form-check-input" id="toggleRA" :checked="activaciones.evaluacionRA" @change="toggle('evaluacionRA', $event.target.checked)">
                                <label class="form-check-label" for="toggleRA">Activar evaluación de RA</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="toggleSeleccion" :checked="activaciones.seleccion" @change="toggle('seleccion', $event.target.checked)">
                                <label class="form-check-label" for="toggleSeleccion">Activar selección de materias</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            esAdmin: false,
            passwordForm: {
                passwordActual: '',
                nuevaPassword: '',
                passwordConfirmacion: ''
            },
            activaciones: {
                evaluacionRA: false,
                seleccion: false
            }
        };
    },

    mounted() {
        this.esAdmin = this.usuario && this.usuario.rol === 'admin';
        this.cargar();
    },

    methods: {
        async cargar() {
            const res = await ConfiguracionAPI.obtener();
            if (res && res.success) {
                this.activaciones = res.data.activaciones;
            }
        },

        guardarPassword() {
            if (this.passwordForm.nuevaPassword !== this.passwordForm.passwordConfirmacion) {
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return;
            }
            const res = ConfiguracionAPI.actualizar_password({
                passwordActual: this.passwordForm.passwordActual,
                nuevaPassword: this.passwordForm.nuevaPassword,
                passwordConfirmacion: this.passwordForm.passwordConfirmacion
            });
            res.then(r => {
                if (r && r.success) {
                    Swal.fire({ icon: 'success', title: 'Contraseña actualizada', timer: 1500, showConfirmButton: false });
                    this.passwordForm = { passwordActual: '', nuevaPassword: '', passwordConfirmacion: '' };
                } else {
                    Swal.fire('Error', r.error, 'error');
                }
            });
        },

        toggle(clave, valor) {
            ConfiguracionAPI.actualizar_activacion(clave, valor).then(r => {
                if (r && r.success) {
                    Swal.fire({ icon: 'success', title: 'Ajuste actualizado', timer: 1000, showConfirmButton: false });
                }
            });
        }
    }
};
