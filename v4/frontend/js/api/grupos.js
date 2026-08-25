// API client para el módulo de Grupos

const GruposAPI = {
    baseUrl: '../backend/api/grupos/',

    async listar() {
        const data = await Http.get(this.baseUrl + 'listar.php', 'include');
        return data.success
            ? { success: true, data: Array.isArray(data.data) ? data.data : [] }
            : { success: false, error: data.error || 'Error de conexión', data: [] };
    },

    async obtener(id) {
        const data = await Http.get(this.baseUrl + `obtener.php?id=${id}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    async guardar(grupo) {
        const data = await Http.post(this.baseUrl + 'guardar.php', grupo, 'include');
        return data.success
            ? { success: true, message: data.message, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    },

    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        return data.success
            ? { success: true, message: data.message, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    }
};
