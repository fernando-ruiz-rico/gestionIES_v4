// API para obtener datos de la aplicación
const AppAPI = {
    baseURL: '../backend/api/',
    
    // Obtener menús
    getMenus: async function() {
        try {
            const response = await fetch(this.baseURL + 'app.php?action=menus', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                return { success: true, data: data.data };
            } else {
                return { success: false, error: data.error };
            }
        } catch (error) {
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },
    
    // Obtener activaciones
    getActivaciones: async function() {
        try {
            const response = await fetch(this.baseURL + 'app.php?action=activaciones', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                return { success: true, data: data.data };
            } else {
                return { success: false, error: data.error };
            }
        } catch (error) {
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    }
};
