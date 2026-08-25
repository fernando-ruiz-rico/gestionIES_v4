// Componente Login View
const LoginView = {
    template: `
        <div class="container d-flex align-items-center min-vh-100 py-5">
            <div class="row justify-content-center w-100">
                <div class="col-12 col-md-8 col-lg-5 col-xxl-4">
                    <div class="text-center mb-4">
                        <h1 class="display-6"><i class="bi bi-mortarboard-fill text-primary"></i> GestionIES</h1>
                        <p class="text-muted">Gestión interna IESSV</p>
                    </div>
                
                    <form @submit.prevent="handleLogin">
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" v-model="username" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" v-model="password" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </button>
                        </div>

                        <div v-if="error" class="alert alert-danger mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ error }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `,
    
    data() {
        return {
            username: '',
            password: '',
            loading: false,
            error: ''
        };
    },
    
    methods: {
        async handleLogin() {
            this.loading = true;
            this.error = '';
            
            try {
                const result = await AuthAPI.login(this.username, this.password);
                this.$emit('login-success', result.data);
            } catch (error) {
                this.error = error.message;
                this.loading = false;
            }
        }
    }
};
