// Aplicación principal Vue 3
const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            isLoggedIn: false,
            usuario: null,
            menus: []
        };
    },
    
    async mounted() {
        // Comprobar si hay sesión activa al cargar la página
        await this.checkAuth();
    },
    
    methods: {
        async checkAuth() {
            const result = await AuthAPI.checkAuth();
            
            if (result.success) {
                this.isLoggedIn = true;
                this.usuario = result.data;
                await this.loadMenus();
            } else {
                this.isLoggedIn = false;
                this.usuario = null;
                this.menus = [];
            }
        },
        
        async loadMenus() {
            const result = await AppAPI.getMenus();
            
            if (result.success) {
                this.menus = result.data.menus;
                this.usuario = { ...this.usuario, ...result.data.usuario };
            }
        },
        
        handleLoginSuccess(userData) {
            this.isLoggedIn = true;
            this.usuario = userData;
            this.loadMenus();
            
            // Mostrar mensaje de bienvenida
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: `Hola ${userData.nombre} ${userData.apellidos}`,
                timer: 2000,
                showConfirmButton: false
            });
        },
        
        async handleLogout() {
            const confirmed = await Swal.fire({
                title: '¿Cerrar sesión?',
                text: 'Se cerrará la sesión actual',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar'
            });
            
            if (confirmed.isConfirmed) {
                await AuthAPI.logout();
                this.isLoggedIn = false;
                this.usuario = null;
                this.menus = [];
                
                Swal.fire({
                    icon: 'success',
                    title: 'Sesión cerrada',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    }
});

// Registrar componentes globales
app.component('login-view', LoginView);
app.component('app-layout', AppLayout);
app.component('sidebar', Sidebar);
app.component('header-bar', HeaderBar);
app.component('home-view', HomeView);
app.component('departamentos-view', DepartamentosView);
app.component('profesores-view', ProfesoresView);
app.component('especialidades-view', EspecialidadesView);
app.component('ciclos-view', CiclosView);
app.component('cursos-view', CursosView);
app.component('grupos-view', GruposView);
app.component('materias-view', MateriasView);
app.component('escenarios-view', EscenariosView);

// Montar la aplicación
app.mount('#app');
