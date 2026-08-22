// Componente Header Bar (barra superior)
const HeaderBar = {
    template: `
        <nav class="navbar navbar-expand-lg border-bottom header-bar">
            <div class="container-fluid">
                <!-- Botón toggle menú -->
                <button class="btn btn-link" id="menu-toggle" @click="toggleMenu">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <!-- Título de la aplicación -->
                <span class="navbar-brand mb-0 h1 ms-3">
                    <i class="bi bi-mortarboard-fill text-primary"></i> GestionIES
                </span>
                
                <!-- Información del usuario a la derecha -->
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 d-none d-md-block">
                        <i class="bi bi-person-circle"></i> {{ usuario.nombre }}
                    </span>
                    <span class="badge bg-secondary me-3">
                        <i class="bi bi-shield-lock"></i> {{ getRolTexto(usuario.rol) }}
                    </span>

                    <!-- Selector de tema (Bootstrap o Bootswatch) -->
                    <div class="dropdown me-2">
                        <button class="btn btn-link" type="button" title="Elegir tema" data-bs-toggle="dropdown">
                            <i class="bi bi-palette-fill"></i>
                        </button>
                        <ul ref="menuTema" class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header fw-bold">Temas claros</li>
                            <li v-for="t in temas.CLAROS" :key="'claro-' + t">
                                <button class="dropdown-item ps-4" type="button" @click="cambiarTema(t)">
                                    <i v-if="t === tema" class="bi bi-check-lg me-2"></i>
                                    {{ temas.nombre(t) }}
                                </button>
                            </li>
                            <li class="dropdown-header fw-bold">Temas oscuros</li>
                            <li v-for="t in temas.OSCUROS" :key="'oscuro-' + t">
                                <button class="dropdown-item ps-4" type="button" @click="cambiarTema(t)">
                                    <i v-if="t === tema" class="bi bi-check-lg me-2"></i>
                                    {{ temas.nombre(t) }}
                                </button>
                            </li>
                        </ul>
                    </div>

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

    data() {
        return {
            tema: window.Temas.actual(),
            temas: window.Temas
        };
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
        },

        // Cambia el tema (CSS de Bootstrap o Bootswatch), lo guarda en el
        // navegador y cierra el desplegable
        cambiarTema(t) {
            this.tema = t;
            window.Temas.guardar(t);
            window.Temas.aplicar(t);
            this.$refs.menuTema.classList.remove('show');
        }
    },
    
    emits: ['logout', 'close-menu']
};
