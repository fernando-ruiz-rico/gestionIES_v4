// API client para el módulo de Competencias por Ciclo (Fase 4.2)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const CompetenciasCiclosAPI = {
    baseUrl: '../backend/api/competencias_ciclos/',

    // Listar ciclos (para el desplegable)
    listar_ciclos() {
        return Http.getOk(this.baseUrl + 'listar_ciclos.php', 'Error al cargar los ciclos', 'include');
    },

    // Listar competencias de un ciclo
    listar(idCiclo) {
        return Http.getOk(this.baseUrl + 'listar.php?idCiclo=' + idCiclo, 'Error al cargar las competencias', 'include');
    },

    // Obtener una competencia
    obtener(id) {
        return Http.getOk(this.baseUrl + 'obtener.php?id=' + id, 'Error al cargar la competencia', 'include');
    },

    // Guardar competencia (crear o actualizar)
    async guardar(data) {
        const res = await Http.post(this.baseUrl + 'guardar.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar la competencia');
        return res;
    },

    // Reordenar competencias (string de orden con prefijo "cm")
    async ordenar(orden) {
        const res = await Http.post(this.baseUrl + 'ordenar.php', { orden: orden }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al reordenar las competencias');
        return res;
    },

    // Eliminar competencia
    async eliminar(id) {
        const res = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar la competencia');
        return res;
    }
};
