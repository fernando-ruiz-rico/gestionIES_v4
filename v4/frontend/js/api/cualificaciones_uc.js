// API client para el módulo de Cualificaciones y Unidades de Competencia (Fase 4.3)

const CualificacionesUCAPI = {
    baseUrl: '../backend/api/cualificaciones_uc.php',

    listar_cualificaciones() {
        return Http.get(this.baseUrl + '?action=listar_cualificaciones', 'include');
    },

    obtener_cualificacion(codigo) {
        return Http.get(this.baseUrl + '?action=obtener_cualificacion&codigo=' + encodeURIComponent(codigo), 'include');
    },

    guardar_cualificacion(data) {
        return Http.post(this.baseUrl + '?action=guardar_cualificacion', data, 'include');
    },

    eliminar_cualificacion(codigo) {
        return Http.post(this.baseUrl + '?action=eliminar_cualificacion', { codigo: codigo }, 'include');
    },

    listar_unidades() {
        return Http.get(this.baseUrl + '?action=listar_unidades', 'include');
    },

    obtener_unidad(codigo) {
        return Http.get(this.baseUrl + '?action=obtener_unidad&codigo=' + encodeURIComponent(codigo), 'include');
    },

    guardar_unidad(data) {
        return Http.post(this.baseUrl + '?action=guardar_unidad', data, 'include');
    },

    eliminar_unidad(codigo) {
        return Http.post(this.baseUrl + '?action=eliminar_unidad', { codigo: codigo }, 'include');
    },

    listar_asociaciones(codigo) {
        return Http.get(this.baseUrl + '?action=listar_asociaciones&codigo=' + encodeURIComponent(codigo), 'include');
    },

    guardar_asociacion(data) {
        return Http.post(this.baseUrl + '?action=guardar_asociacion', data, 'include');
    },

    eliminar_asociacion(data) {
        return Http.post(this.baseUrl + '?action=eliminar_asociacion', data, 'include');
    }
};
