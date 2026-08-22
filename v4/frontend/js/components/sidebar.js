// Componente Sidebar (menú lateral)
const Sidebar = {
    template: `
        <div class="border-end" id="sidebar-wrapper">
            <div class="p-3 text-center border-bottom">
                <strong class="d-block text-uppercase">Gestión IES</strong>
                <em>{{ usuario.loginUsuario }}</em>
            </div>
            <div class="list-group list-group-flush">
                <template v-for="menu in menus" :key="menu.id + '-' + menu.texto + '-' + (menu.submenu ? 'sub' : 'main')">
                    <!-- Menú principal con link -->
                    <a v-if="!menu.submenu && menu.link" 
                       href="#" 
                       @click.prevent="navigate(menu.link)"
                       :class="['list-group-item', 'list-group-item-action', { active: menu.link === linkActual }]">
                        <i :class="'bi ' + menu.icono"></i>
                        {{ menu.texto }}
                    </a>
                    
                    <!-- Menú principal sin link (desplegable) -->
                    <a v-else-if="!menu.submenu && !menu.link" 
                       href="#" 
                       class="list-group-item list-group-item-action"
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
                       :class="['list-group-item', 'list-group-item-action', 'ps-4', { active: menu.link === linkActual }]"
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
        },
        linkActual: {
            type: String,
            default: ''
        }
    },
    
    data() {
        return {
            submenuAbierto: null
        };
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
    
    emits: ['navigate', 'close-menu']
};
