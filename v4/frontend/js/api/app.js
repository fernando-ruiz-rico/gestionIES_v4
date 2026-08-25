// API para obtener datos de la aplicación
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }. El manejo del error lo hace
// la vista con try/catch.
const AppAPI = {
    baseURL: '../backend/api/',

    // Obtener menús
    getMenus() {
        return Http.getOk(this.baseURL + 'app/menus.php', 'Error al cargar los menús');
    },

    // Obtener activaciones
    getActivaciones() {
        return Http.getOk(this.baseURL + 'app/activaciones.php', 'Error al cargar las activaciones');
    }
};
