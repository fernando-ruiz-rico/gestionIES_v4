// Componente App Layout (layout principal de la aplicación)
const AppLayout = {
    template: `
        <div id="wrapper">
            <!-- Header superior -->
            <header-bar :usuario="usuario" @logout="handleLogout" @close-menu="handleCloseMenu"></header-bar>
            
            <!-- Sidebar con menú -->
            <sidebar :usuario="usuario" :menus="menus" @navigate="handleNavigate" @close-menu="handleCloseMenu"></sidebar>
            
            <!-- Contenido principal -->
            <div id="page-content-wrapper" class="container-fluid">
                <component :is="vistaActual" :usuario="usuario"></component>
            </div>
        </div>
    `,
    
    props: {
        usuario: {
            type: Object,
            required: true
        },
        menus: {
            type: Array,
            required: true
        }
    },
    
    data() {
        return {
            vistaActual: 'home-view'
        };
    },
    
    components: {
        'home-view': HomeView,
        'departamentos-view': DepartamentosView,
        'profesores-view': ProfesoresView,
        'especialidades-view': EspecialidadesView,
        'ciclos-view': CiclosView,
        'cursos-view': CursosView,
        'grupos-view': GruposView,
        'materias-view': MateriasView,
        'escenarios-view': EscenariosView,
        'programaciones-view': ProgramacionesView,
        'programaciones-apartados-view': ProgramacionesApartadosView,
        'programaciones-contenidos-defecto-view': ProgramacionesContenidosDefectoView,
        'temas-contenidos-defecto-view': TemasContenidosDefectoView,
        'programaciones-aula-view': ProgramacionesAulaView,
        'programaciones-seguimiento-view': ProgramacionesSeguimientoView,
        'temas-view': TemasView,
        'pccf-view': PCCFView,
        'pccf-apartados-view': PCCFApartadosView,
        'pccf-contenidos-defecto-view': PCCFContenidosDefectoView
    },

    methods: {
        handleLogout() {
            this.$emit('logout');
        },

        handleCloseMenu() {
            // Cierra el menú lateral al navegar o por evento explícito
            document.getElementById('wrapper').classList.remove('toggled');
        },

        handleNavigate(link) {
            // Mapear links a vistas
            const vistaMap = {
                'departamentos.php': 'departamentos-view',
                'profesores.php': 'profesores-view',
                'especialidades.php': 'especialidades-view',
                'ciclos.php': 'ciclos-view',
                'cursos.php': 'cursos-view',
                'grupos.php': 'grupos-view',
                'materias.php': 'materias-view',
                'escenarios.php': 'escenarios-view',
                'programaciones.php': 'programaciones-view',
                'programaciones_apartados.php': 'programaciones-apartados-view',
                'programaciones_contenidos_defecto.php': 'programaciones-contenidos-defecto-view',
                'temas_contenidos_defecto.php': 'temas-contenidos-defecto-view',
                'programaciones_aula.php': 'programaciones-aula-view',
                'programaciones_seguimiento_aula.php': 'programaciones-seguimiento-view',
                'temas.php': 'temas-view',
                'pccf_apartados.php': 'pccf-apartados-view',
                'pccf_contenidos_defecto.php': 'pccf-contenidos-defecto-view',
                'pccf.php': 'pccf-view'
            };
            
            this.vistaActual = vistaMap[link] || 'home-view';
            console.log('Navegar a:', link, 'Vista:', this.vistaActual);
        }
    },
    
    mounted() {
        // Cargar datos iniciales si es necesario
    }
};
