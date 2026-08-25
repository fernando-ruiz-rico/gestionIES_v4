// API para autenticación
const AuthAPI = {
    baseURL: '../backend/api/',

    // Login de usuario
    login(username, password) {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        return Http.post(this.baseURL + 'auth.php?action=login', formData);
    },

    // Logout de usuario
    async logout() {
        const data = await Http.get(this.baseURL + 'auth.php?action=logout');
        return data.success;
    },

    // Comprobar sesión activa
    checkAuth() {
        return Http.get(this.baseURL + 'auth.php?action=check');
    }
};
