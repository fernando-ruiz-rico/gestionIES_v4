// API client para el módulo de Grupos
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const GruposAPI = {
    baseUrl: '../backend/api/grupos/',

    // Listar grupos
    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al cargar los grupos', 'include');
    },

    // Obtener un grupo
    obtener(id) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${id}`, 'Error al cargar el grupo', 'include');
    },

    // Guardar grupo (crear o editar)
    async guardar(grupo) {
        const data = await Http.post(this.baseUrl + 'guardar.php', grupo, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar el grupo');
        return data;
    },

    // Eliminar grupo
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar el grupo');
        return data;
    }
};
