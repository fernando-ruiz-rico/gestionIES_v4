// API client para el módulo de Actas de departamentos (Fase 6.1)

const ActasAPI = {
    baseUrl: '../backend/api/actas.php',

    // Lista las actas de un departamento (más reciente primero)
    async listar(idDepartamento) {
        const data = await Http.get(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: [] };
    },

    // Devuelve el texto y fecha de un acta
    async obtener(idActa) {
        const data = await Http.get(this.baseUrl + '?action=obtener&idActa=' + idActa, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    // Inserta o actualiza un acta de departamento
    async guardar(data) {
        const parsed = await Http.post(this.baseUrl + '?action=guardar', data, 'include');
        return parsed.success
            ? { success: true, data: parsed.data }
            : { success: false, error: parsed.error || 'Error de conexión' };
    }
};
