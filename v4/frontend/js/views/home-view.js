// Componente Home View (página de inicio)
const HomeView = {
    template: `
        <section class="inicio">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body text-center p-5">
                                <h1 class="display-5 mb-3">
                                    <i class="bi bi-mortarboard-fill text-primary"></i> 
                                    Bienvenid@ a GestionIES
                                </h1>
                                <p class="lead text-muted mb-4">
                                    Sistema de gestión interna IESSV
                                </p>
                                <p class="mb-4">
                                    Hola, <strong>{{ usuario.nombre }} {{ usuario.apellidos }}</strong>
                                </p>
                                <p class="text-muted mb-4">
                                    <i class="bi bi-info-circle"></i> 
                                    Escoge una opción del menú lateral.
                                </p>
                                
                                <!-- Accesos rápidos -->
                                <div class="row g-3 mt-4">
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-file-earmark-text fs-4 d-block mb-2"></i>
                                            <small>Programaciones</small>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-journal-text fs-4 d-block mb-2"></i>
                                            <small>PCCF</small>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-hand-index fs-4 d-block mb-2"></i>
                                            <small>Desideratas</small>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-book fs-4 d-block mb-2"></i>
                                            <small>Actas</small>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-person-badge fs-4 d-block mb-2"></i>
                                            <small>Perfil</small>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-question-circle fs-4 d-block mb-2"></i>
                                            <small>Ayuda</small>
                                        </a>
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
    
    data() {
        return {
            // Datos locales si son necesarios
        };
    },
    
    methods: {
        // Métodos si son necesarios
    },
    
    mounted() {
        // Código a ejecutar cuando el componente se monta
    }
};
