// API client para el módulo de Grupos

const GruposAPI = {
    baseUrl: '../backend/api/grupos/',

    async listar() {
        try {
            const response = await fetch(this.baseUrl + 'listar.php', { credentials: 'include' });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: [] };
            }
            return { success: true, data: Array.isArray(data.data) ? data.data : [] };
        } catch (e) {
            console.error(e);
            return { success: false, error: 'Error de conexión', data: [] };
        }
    },

    async obtener(id) {
        try {
            const response = await fetch(this.baseUrl + `obtener.php?id=${id}`, { credentials: 'include' });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    async guardar(grupo) {
        try {
            const response = await fetch(this.baseUrl + 'guardar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(grupo)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return { success: true, message: data.message, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async eliminar(id) {
        try {
            const response = await fetch(this.baseUrl + 'eliminar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return { success: true, message: data.message, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    }
};
