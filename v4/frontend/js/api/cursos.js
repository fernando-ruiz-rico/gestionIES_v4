// API client para el módulo de Cursos
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const CursosAPI = {
    baseUrl: '../backend/api/cursos/',

    // Listar cursos
    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al cargar los cursos', 'include');
    },

    // Obtener un curso
    obtener(id) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${id}`, 'Error al cargar el curso', 'include');
    },

    // Guardar curso (crear o editar)
    async guardar(curso) {
        const data = await Http.post(this.baseUrl + 'guardar.php', curso, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar el curso');
        return data;
    },

    // Eliminar curso
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar el curso');
        return data;
    }
};
