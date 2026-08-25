// API client para el módulo de Especialidades
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const EspecialidadesAPI = {
    baseUrl: '../backend/api/especialidades/',

    // Listar todas las especialidades
    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al cargar las especialidades', 'include');
    },

    // Obtener una especialidad por ID
    obtener(idEspecialidad) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${idEspecialidad}`, 'Error al cargar la especialidad', 'include');
    },

    // Guardar especialidad (crear o editar)
    async guardar(especialidad) {
        const data = await Http.post(this.baseUrl + 'guardar.php', especialidad, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar la especialidad');
        return data;
    },

    // Eliminar especialidad
    async eliminar(idEspecialidad) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: idEspecialidad }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar la especialidad');
        return data;
    }
};
