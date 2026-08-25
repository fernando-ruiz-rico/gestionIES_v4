// API client para el módulo de Especialidades

const EspecialidadesAPI = {
    baseUrl: '../backend/api/especialidades/',

    // Listar todas las especialidades
    async listar() {
        const data = await Http.get(this.baseUrl + 'listar.php', 'include');
        return data.success
            ? { success: true, data: Array.isArray(data.data) ? data.data : [] }
            : { success: false, error: data.error || 'Error de conexión', data: [] };
    },

    // Obtener una especialidad por ID
    async obtener(idEspecialidad) {
        const data = await Http.get(this.baseUrl + `obtener.php?id=${idEspecialidad}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error };
    },

    // Guardar especialidad (crear o editar)
    async guardar(especialidad) {
        const data = await Http.post(this.baseUrl + 'guardar.php', especialidad, 'include');
        return data.success
            ? { success: true, message: data.message, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    },

    // Eliminar especialidad
    async eliminar(idEspecialidad) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: idEspecialidad }, 'include');
        return data.success
            ? { success: true, message: data.message, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    }
};
