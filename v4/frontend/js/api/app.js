// API para obtener datos de la aplicación
const AppAPI = {
    baseURL: '../backend/api/',

    // Obtener menús
    getMenus() {
        return Http.get(this.baseURL + 'app.php?action=menus');
    },

    // Obtener activaciones
    getActivaciones() {
        return Http.get(this.baseURL + 'app.php?action=activaciones');
    }
};
