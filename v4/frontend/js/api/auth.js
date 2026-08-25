// API para autenticación
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }. El manejo del error lo hace
// la vista con try/catch.
const AuthAPI = {
    baseURL: '../backend/api/',

    // Login de usuario (acción)
    async login(username, password) {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        const data = await Http.post(this.baseURL + 'auth.php?action=login', formData);
        if (!data.success) throw new Error(data.error || 'Error al iniciar sesión');
        return data;
    },

    // Logout de usuario (acción)
    async logout() {
        const data = await Http.get(this.baseURL + 'auth.php?action=logout');
        if (!data.success) throw new Error(data.error || 'Error al cerrar la sesión');
        return data;
    },

    // Comprobar sesión activa: resuelve con el usuario (data.data)
    checkAuth() {
        return Http.getOk(this.baseURL + 'auth.php?action=check', 'No hay sesión activa');
    }
};
