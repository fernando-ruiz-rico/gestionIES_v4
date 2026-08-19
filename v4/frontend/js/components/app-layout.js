// Componente App Layout (layout principal de la aplicación)
const AppLayout = {
    template: `
        <div id="wrapper">
            <!-- Header superior -->
            <header-bar :usuario="usuario" @logout="handleLogout"></header-bar>
            
            <!-- Sidebar con menú -->
            <sidebar :usuario="usuario" :menus="menus" @navigate="handleNavigate"></sidebar>
            
            <!-- Contenido principal -->
            <div id="page-content-wrapper" class="container-fluid">
                <home-view :usuario="usuario"></home-view>
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
    
    components: {
        'home-view': HomeView
    },
    
    methods: {
        handleLogout() {
            this.$emit('logout');
        },
        
        handleNavigate(link) {
            // Aquí se podría implementar navegación por rutas
            console.log('Navegar a:', link);
        }
    },
    
    mounted() {
        // Cargar datos iniciales si es necesario
    }
};
