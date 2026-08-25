// API client para el módulo de Escenarios

const EscenariosAPI = {
    baseUrl: '../backend/api/escenarios/',

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

    async guardar(escenario) {
        return Http.post(this.baseUrl + 'guardar.php', escenario, 'include');
    },

    async eliminar(id) {
        return Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
    }
};
