// API client para el módulo de Competencias por Ciclo (Fase 4.2)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const CompetenciasCiclosAPI = {
    baseUrl: '../backend/api/competencias_ciclos.php',

    // Listar ciclos (para el desplegable)
    listar_ciclos() {
        return Http.getOk(this.baseUrl + '?action=listar_ciclos', 'Error al cargar los ciclos', 'include');
    },

    // Listar competencias de un ciclo
    listar(idCiclo) {
        return Http.getOk(this.baseUrl + '?action=listar&idCiclo=' + idCiclo, 'Error al cargar las competencias', 'include');
    },

    // Obtener una competencia
    obtener(id) {
        return Http.getOk(this.baseUrl + '?action=obtener&id=' + id, 'Error al cargar la competencia', 'include');
    },

    // Guardar competencia (crear o actualizar)
    async guardar(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar la competencia');
        return res;
    },

    // Reordenar competencias (string de orden con prefijo "cm")
    async ordenar(orden) {
        const res = await Http.post(this.baseUrl + '?action=ordenar', { orden: orden }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al reordenar las competencias');
        return res;
    },

    // Eliminar competencia
    async eliminar(id) {
        const res = await Http.post(this.baseUrl + '?action=eliminar', { id: id }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar la competencia');
        return res;
    }
};
