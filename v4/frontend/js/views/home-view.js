// Componente Home View (página de inicio)
const HomeView = {
    template: `
        <section class="d-flex align-items-center justify-content-center min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center p-5">
                                <h1 class="display-6 mb-2">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    Bienvenid@ a GestionIES
                                </h1>
                                <p class="lead text-muted mb-4">
                                    Sistema de gestión interna IESSV
                                </p>
                                <p class="mb-3">
                                    Hola, <strong>{{ usuario.nombre }} {{ usuario.apellidos }}</strong>
                                </p>
                                <p class="text-muted mb-4">
                                    <i class="bi bi-info-circle"></i>
                                    Escoge una opción del menú lateral o un acceso rápido.
                                </p>

                                <!-- Accesos rápidos -->
                                <div class="row g-2 mt-4">
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="irA('programaciones.php')">
                                            <i class="bi bi-file-earmark-text fs-4 d-block mb-1"></i>
                                            <small>Programaciones</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="irA('pccf.php')">
                                            <i class="bi bi-journal-text fs-4 d-block mb-1"></i>
                                            <small>PCCF</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="irA('seleccion.php')">
                                            <i class="bi bi-hand-index fs-4 d-block mb-1"></i>
                                            <small>Desideratas</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="irA('actas.php')">
                                            <i class="bi bi-book fs-4 d-block mb-1"></i>
                                            <small>Actas</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="irA('configuracion.php')">
                                            <i class="bi bi-person-badge fs-4 d-block mb-1"></i>
                                            <small>Perfil</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 py-3" @click="ayuda">
                                            <i class="bi bi-question-circle fs-4 d-block mb-1"></i>
                                            <small>Ayuda</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `,

    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    methods: {
        irA(link) {
            this.$emit('navigate', link);
        },

        ayuda() {
            Swal.fire({
                title: 'Ayuda',
                text: 'La sección de ayuda está en construcción. Por ahora, utiliza el menú lateral para navegar.',
                icon: 'info',
                timer: 3000,
                showConfirmButton: false
            });
        }
    }
};
