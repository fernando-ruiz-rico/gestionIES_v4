// Componente Sidebar (menú lateral)
const Sidebar = {
    template: `
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading">
                Gestión IES<br />
                <em>{{ usuario.loginUsuario }}</em>
            </div>
            <div class="list-group list-group-flush">
                <template v-for="menu in menusFiltrados" :key="menu.id + '-' + menu.texto + '-' + (menu.submenu ? 'sub' : 'main')">
                    <!-- Menú principal con link -->
                    <a v-if="!menu.submenu && menu.link" 
                       href="#" 
                       @click.prevent="navigate(menu.link)"
                       class="list-group-item list-group-item-action bg-light">
                        <i :class="'bi ' + menu.icono"></i>
                        {{ menu.texto }}
                    </a>
                    
                    <!-- Menú principal sin link (desplegable) -->
                    <a v-else-if="!menu.submenu && !menu.link" 
                       href="#" 
                       class="list-group-item list-group-item-action bg-light"
                       @click.prevent="toggleSubmenu(menu.id)">
                        <i :class="'bi ' + menu.icono"></i>
                        {{ menu.texto }}
                        <i v-if="hasSubmenu(menu.id)" 
                           :class="submenuAbierto === menu.id ? 'bi bi-chevron-down float-end' : 'bi bi-chevron-right float-end'"></i>
                    </a>
                    
                    <!-- Submenús -->
                    <a v-else-if="menu.submenu" 
                       v-show="submenuAbierto === getParentMenuId(menu.id)"
                       href="#"
                       class="list-group-item list-group-item-action submenu"
                       @click.prevent="navigate(menu.link)">
                        <i :class="'bi ' + menu.icono"></i>
                        {{ menu.texto }}
                    </a>
                </template>
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
            submenuAbierto: null
        };
    },
    
    computed: {
        menusFiltrados() {
            // Los menús ya vienen filtrados por rol desde el backend
            return this.menus;
        }
    },
    
    methods: {
        toggleSubmenu(id) {
            if (this.submenuAbierto === id) {
                this.submenuAbierto = null;
            } else {
                this.submenuAbierto = id;
            }
        },
        
        hasSubmenu(id) {
            return this.menus.some(m => m.submenu && m.id === id);
        },
        
        getParentMenuId(submenuId) {
            // Buscar el menú padre que contiene este submenu
            const parent = this.menus.find(m => !m.submenu && m.id === submenuId);
            return parent ? parent.id : submenuId;
        },
        
        navigate(link) {
            if (link && !link.startsWith('javascript:')) {
                this.$emit('navigate', link + '.php');
                // Cerrar menú después de navegar - emitir evento para app-layout
                this.$emit('close-menu');
            }
        }
    },
    
    emits: ['navigate', 'close-menu'],
    
    mounted() {
        // Escuchar cambios de ruta para cerrar menú automáticamente
        window.addEventListener('hashchange', () => {
            this.$emit('close-menu');
        });
    }
};
