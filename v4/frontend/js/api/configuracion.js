// API client para el módulo de Configuración (Fase 7.3)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ConfiguracionAPI = {
    baseUrl: '../backend/api/configuracion.php',

    // Obtener las activaciones actuales
    obtener() {
        return Http.getOk(this.baseUrl + '?action=obtener', 'Error al obtener la configuración', 'include');
    },

    // Actualizar la contraseña del usuario
    async actualizar_password(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_password', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar la contraseña');
        return res;
    },

    // Activar/desactivar un ajuste de la aplicación
    async actualizar_activacion(clave, valor) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_activacion', { clave: clave, valor: valor }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar el ajuste');
        return res;
    }
};
