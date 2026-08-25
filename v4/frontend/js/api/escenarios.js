// API client para el módulo de Escenarios de Desideratas
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const EscenariosAPI = {
    baseUrl: '../backend/api/escenarios/',

    // Listar escenarios de un departamento
    listar(idDepartamento) {
        const url = this.baseUrl + 'listar.php' + ((idDepartamento > 0) ? `?idDepartamento=${idDepartamento}` : '');
        return Http.getOk(url, 'Error al cargar los escenarios', 'include');
    },

    // Obtener un escenario
    obtener(id) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${id}`, 'Error al cargar el escenario', 'include');
    },

    // Guardar escenario (crear o editar)
    async guardar(escenario) {
        const data = await Http.post(this.baseUrl + 'guardar.php', escenario, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar el escenario');
        return data;
    },

    // Eliminar escenario
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar el escenario');
        return data;
    },

    // Cambia el estado del escenario (actual / activo_desideratas / modo_rueda)
    async alternar(id, campo) {
        const data = await Http.post(this.baseUrl + 'alternar.php', { id: id, campo: campo }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al actualizar el escenario');
        return data;
    },

    // Duplicar escenario (fiel a v3)
    async duplicar(id) {
        const data = await Http.post(this.baseUrl + 'duplicar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al duplicar el escenario');
        return data;
    }
};
