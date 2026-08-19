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
        'departamentos-view': DepartamentosView
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
            if (link === 'departamentos.php') {
                this.vistaActual = 'departamentos-view';
            } else {
                this.vistaActual = 'home-view';
            }
            console.log('Navegar a:', link, 'Vista:', this.vistaActual);
        }
    },
    
    mounted() {
        // Cargar datos iniciales si es necesario
    }
};
