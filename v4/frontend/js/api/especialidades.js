// API client para el módulo de Especialidades

const EspecialidadesAPI = {
    baseUrl: '../backend/api/especialidades/',

    // Listar todas las especialidades
    async listar() {
        try {
            const response = await fetch(this.baseUrl + 'listar.php', {
                method: 'GET',
                credentials: 'include'
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: [] };
            }
            // Mismo formato de respuesta que el resto de clientes de la app
            return { success: true, data: Array.isArray(data.data) ? data.data : [] };
        } catch (error) {
            console.error('Error al listar especialidades:', error);
            return { success: false, error: 'Error de conexión', data: [] };
        }
    },

    // Obtener una especialidad por ID
    async obtener(idEspecialidad) {
        try {
            const response = await fetch(this.baseUrl + `obtener.php?id=${idEspecialidad}`, {
                method: 'GET',
                credentials: 'include'
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || null };
            }
            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error al obtener especialidad:', error);
            return null;
        }
    },

    // Guardar especialidad (crear o editar)
    async guardar(especialidad) {
        try {
            const response = await fetch(this.baseUrl + 'guardar.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(especialidad)
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return { success: true, message: data.message, data: data.data };
        } catch (error) {
            console.error('Error al guardar especialidad:', error);
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Eliminar especialidad
    async eliminar(idEspecialidad) {
        try {
            const response = await fetch(this.baseUrl + 'eliminar.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: idEspecialidad })
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return { success: true, message: data.message, data: data.data };
        } catch (error) {
            console.error('Error al eliminar especialidad:', error);
            return { success: false, error: 'Error de conexión' };
        }
    }
};
