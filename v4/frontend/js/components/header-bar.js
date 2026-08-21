// Componente Header Bar (barra superior)
const HeaderBar = {
    template: `
        <nav class="navbar navbar-expand-lg navbar-light bg-light header-bar">
            <div class="container-fluid">
                <!-- Botón toggle menú -->
                <button class="btn btn-light" id="menu-toggle" @click="toggleMenu">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <!-- Título de la aplicación -->
                <span class="navbar-brand mb-0 h1 ms-3">
                    <i class="bi bi-mortarboard-fill text-primary"></i> GestionIES
                </span>
                
                <!-- Información del usuario a la derecha -->
                <div class="ms-auto d-flex align-items-center">
                    <span class="text-muted me-3 d-none d-md-block">
                        <i class="bi bi-person-circle"></i> {{ usuario.nombre }}
                    </span>
                    <span class="badge bg-secondary me-3">
                        <i class="bi bi-shield-lock"></i> {{ getRolTexto(usuario.rol) }}
                    </span>
                    <button class="btn btn-outline-danger btn-sm" @click="logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-md-inline">Salir</span>
                    </button>
                </div>
            </div>
        </nav>
    `,
    
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },
    
    methods: {
        toggleMenu() {
            document.getElementById('wrapper').classList.toggle('toggled');
        },
        
        getRolTexto(rol) {
            const roles = {
                'admin': 'Administrador',
                'jefeDepartamento': 'Jefe Depto.',
                'profesor': 'Profesor'
            };
            return roles[rol] || rol;
        },
        
        logout() {
            this.$emit('logout');
        },
        
        closeMenu() {
            // Cierra el menú lateral (útil para móvil)
            document.getElementById('wrapper').classList.remove('toggled');
        }
    },
    
    emits: ['logout', 'close-menu']
};
