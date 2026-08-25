// API client para el módulo de Cualificaciones y Unidades de Competencia (Fase 4.3)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const CualificacionesUCAPI = {
    baseUrl: '../backend/api/cualificaciones_uc.php',

    // Listar cualificaciones
    listar_cualificaciones() {
        return Http.getOk(this.baseUrl + '?action=listar_cualificaciones', 'Error al cargar las cualificaciones', 'include');
    },

    // Obtener una cualificación
    obtener_cualificacion(codigo) {
        return Http.getOk(this.baseUrl + '?action=obtener_cualificacion&codigo=' + encodeURIComponent(codigo), 'Error al cargar la cualificación', 'include');
    },

    // Guardar cualificación (crear o actualizar)
    async guardar_cualificacion(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar_cualificacion', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar la cualificación');
        return res;
    },

    // Eliminar cualificación
    async eliminar_cualificacion(codigo) {
        const res = await Http.post(this.baseUrl + '?action=eliminar_cualificacion', { codigo: codigo }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar la cualificación');
        return res;
    },

    // Listar unidades de competencia
    listar_unidades() {
        return Http.getOk(this.baseUrl + '?action=listar_unidades', 'Error al cargar las unidades', 'include');
    },

    // Obtener una unidad
    obtener_unidad(codigo) {
        return Http.getOk(this.baseUrl + '?action=obtener_unidad&codigo=' + encodeURIComponent(codigo), 'Error al cargar la unidad', 'include');
    },

    // Guardar unidad (crear o actualizar)
    async guardar_unidad(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar_unidad', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar la unidad');
        return res;
    },

    // Eliminar unidad
    async eliminar_unidad(codigo) {
        const res = await Http.post(this.baseUrl + '?action=eliminar_unidad', { codigo: codigo }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar la unidad');
        return res;
    },

    // Listar unidades asociadas a una cualificación
    listar_asociaciones(codigo) {
        return Http.getOk(this.baseUrl + '?action=listar_asociaciones&codigo=' + encodeURIComponent(codigo), 'Error al cargar las asociaciones', 'include');
    },

    // Asociar una unidad a una cualificación
    async guardar_asociacion(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar_asociacion', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al asociar la unidad');
        return res;
    },

    // Desasociar una unidad de una cualificación
    async eliminar_asociacion(data) {
        const res = await Http.post(this.baseUrl + '?action=eliminar_asociacion', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al desasociar la unidad');
        return res;
    }
};
