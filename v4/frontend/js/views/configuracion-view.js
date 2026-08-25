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
                        <div class="card-header"><h5 class="h6 mb-0">Cambiar contraseña</h5></div>
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
                        <div class="card-header"><h5 class="h6 mb-0">Ajustes de aplicación</h5></div>
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
            try {
                const data = await ConfiguracionAPI.obtener();
                this.activaciones = data.activaciones;
            } catch (error) {
                // Si falla, se mantienen los valores por defecto
            }
        },

        guardarPassword() {
            if (this.passwordForm.nuevaPassword !== this.passwordForm.passwordConfirmacion) {
                Avisos.error('Las contraseñas no coinciden');
                return;
            }
            ConfiguracionAPI.actualizar_password({
                passwordActual: this.passwordForm.passwordActual,
                nuevaPassword: this.passwordForm.nuevaPassword,
                passwordConfirmacion: this.passwordForm.passwordConfirmacion
            }).then(() => {
                Avisos.exito('Contraseña actualizada');
                this.passwordForm = { passwordActual: '', nuevaPassword: '', passwordConfirmacion: '' };
            }).catch((error) => {
                Avisos.error(error.message);
            });
        },

        toggle(clave, valor) {
            ConfiguracionAPI.actualizar_activacion(clave, valor).then(() => {
                Avisos.exito('Ajuste actualizado');
            }).catch((error) => {
                Avisos.error(error.message);
            });
        }
    }
};
