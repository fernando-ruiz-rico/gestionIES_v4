// API client para el módulo de Departamentos
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const DepartamentosAPI = {
    baseUrl: '../backend/api/departamentos/',

    // Listar departamentos
    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al cargar los departamentos');
    },

    // Obtener un departamento
    obtener(idDepartamento) {
        return Http.getOk(this.baseUrl + 'obtener.php?id=' + idDepartamento, 'Error al cargar el departamento');
    },

    // Guardar departamento (alta o edición)
    async guardar(datos) {
        const data = await Http.post(this.baseUrl + 'guardar.php', datos);
        if (!data.success) throw new Error(data.error || 'Error al guardar el departamento');
        return data;
    },

    // Eliminar departamento
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id });
        if (!data.success) throw new Error(data.error || 'Error al eliminar el departamento');
        return data;
    }
};
