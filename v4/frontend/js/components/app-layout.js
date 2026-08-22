// Componente App Layout (layout principal de la aplicación)
const AppLayout = {
    template: `
        <div id="wrapper">
            <!-- Header superior -->
            <header-bar :usuario="usuario" @logout="handleLogout" @close-menu="handleCloseMenu"></header-bar>
            
            <!-- Sidebar con menú -->
            <sidebar :usuario="usuario" :menus="menus" :link-actual="linkActual" @navigate="handleNavigate" @close-menu="handleCloseMenu"></sidebar>
            
            <!-- Contenido principal -->
            <div id="page-content-wrapper" class="container-fluid">
                <component :is="vistaActual" :usuario="usuario" @navigate="handleNavigate"></component>
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
            vistaActual: 'home-view',
            linkActual: ''
        };
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
            // Recordar el enlace activo para resaltar el elemento del menú
            this.linkActual = link || '';

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
                'pccf.php': 'pccf-view',
                'resultados_aprendizaje.php': 'resultados_aprendizaje-view',
                'competencias_ciclos.php': 'competencias_ciclos-view',
                'cualificaciones_uc.php': 'cualificaciones_uc-view',
                'seleccion.php': 'seleccion-view',
                'actas.php': 'actas-view',
                'historico.php': 'historico-view',
                'estadisticas.php': 'estadisticas-view',
                'configuracion.php': 'configuracion-view',
                'excel.php': 'excel-view'
            };
            
            this.vistaActual = vistaMap[link] || 'home-view';
        }
    }
};
