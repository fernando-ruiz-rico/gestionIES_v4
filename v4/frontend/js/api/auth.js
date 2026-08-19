// API para autenticación
const AuthAPI = {
    baseURL: '../backend/api/',
    
    // Login de usuario
    login: async function(username, password) {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        
        try {
            const response = await fetch(this.baseURL + 'auth.php?action=login', {
                method: 'POST',
                body: formData,
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
    
    // Logout de usuario
    logout: async function() {
        try {
            const response = await fetch(this.baseURL + 'auth.php?action=logout', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error en logout:', error);
            return false;
        }
    },
    
    // Comprobar sesión activa
    checkAuth: async function() {
        try {
            const response = await fetch(this.baseURL + 'auth.php?action=check', {
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
            return { success: false, error: 'Error de conexión' };
        }
    }
};
