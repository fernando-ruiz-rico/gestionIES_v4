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
            try {
                this.usuario = await AuthAPI.checkAuth();
                this.isLoggedIn = true;
                await this.loadMenus();
            } catch (error) {
                // Sin sesión activa (o error de conexión): queda en el login
                this.isLoggedIn = false;
                this.usuario = null;
                this.menus = [];
            }
        },
        
        async loadMenus() {
            const data = await AppAPI.getMenus();
            this.menus = data.menus || [];
            this.usuario = { ...this.usuario, ...data.usuario };
        },
        
        async handleLoginSuccess(userData) {
            this.isLoggedIn = true;
            this.usuario = userData;
            try {
                await this.loadMenus();
            } catch (error) {
                // Si fallan los menús, se muestran vacíos (igual que antes)
            }
            
            // Mostrar mensaje de bienvenida
            Avisos.exito('¡Bienvenido!', `Hola ${userData.nombre}`);
        },
        
        async handleLogout() {
            const confirmed = await Avisos.confirmar('¿Cerrar sesión?', 'Se cerrará la sesión actual', { boton: 'Sí, cerrar', icono: 'question' });
            
            if (confirmed.isConfirmed) {
                try {
                    await AuthAPI.logout();
                } catch (error) {
                    // La sesión local se cierra igualmente
                    console.error('Error al cerrar la sesión:', error);
                }
                this.isLoggedIn = false;
                this.usuario = null;
                this.menus = [];
                
                Avisos.exito('Sesión cerrada');
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
app.component('perfil-view', PerfilView);
app.component('departamentos-view', DepartamentosView);
app.component('profesores-view', ProfesoresView);
app.component('especialidades-view', EspecialidadesView);
app.component('ciclos-view', CiclosView);
app.component('cursos-view', CursosView);
app.component('grupos-view', GruposView);
app.component('materias-view', MateriasView);
app.component('escenarios-view', EscenariosView);
app.component('programaciones-view', ProgramacionesView);
app.component('programaciones-apartados-view', ProgramacionesApartadosView);
app.component('programaciones-contenidos-defecto-view', ProgramacionesContenidosDefectoView);
app.component('programaciones-aula-view', ProgramacionesAulaView);
app.component('programaciones-seguimiento-view', ProgramacionesSeguimientoView);
app.component('temas-view', TemasView);
app.component('temas-aula-view', TemasAulaView);
app.component('temas-contenidos-defecto-view', TemasContenidosDefectoView);
app.component('pccf-view', PCCFView);
app.component('pccf-apartados-view', PCCFApartadosView);
app.component('pccf-contenidos-defecto-view', PCCFContenidosDefectoView);
app.component('resultados_aprendizaje-view', ResultadosArendizajeView);
app.component('competencias_ciclos-view', CompetenciasCiclosView);
app.component('cualificaciones_uc-view', CualificacionesUCView);
app.component('seleccion-view', SeleccionView);
app.component('actas-view', ActasView);
app.component('historico-view', HistoricoView);
app.component('estadisticas-view', EstadisticasView);
app.component('configuracion-view', ConfiguracionView);
app.component('ayuda-view', AyudaView);

// Montar la aplicación
app.mount('#app');
