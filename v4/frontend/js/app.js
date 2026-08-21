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
                text: `Hola ${userData.nombre}`,
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
app.component('programaciones-view', ProgramacionesView);
app.component('programaciones-apartados-view', ProgramacionesApartadosView);
app.component('programaciones-contenidos-defecto-view', ProgramacionesContenidosDefectoView);
app.component('programaciones-aula-view', ProgramacionesAulaView);
app.component('programaciones-seguimiento-view', ProgramacionesSeguimientoView);
app.component('temas-view', TemasView);
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
app.component('excel-view', ExcelView);
app.component('modal-confirmacion', ModalConfirmacion);
app.component('modal-mensaje', ModalMensaje);

// Montar la aplicación
app.mount('#app');
