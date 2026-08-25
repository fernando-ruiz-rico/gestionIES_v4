// API client para el módulo de Configuración (Fase 7.3)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ConfiguracionAPI = {
    baseUrl: '../backend/api/configuracion/',

    // Obtener las activaciones actuales
    obtener() {
        return Http.getOk(this.baseUrl + 'obtener.php', 'Error al obtener la configuración', 'include');
    },

    // Actualizar la contraseña del usuario
    async actualizar_password(data) {
        const res = await Http.post(this.baseUrl + 'actualizar_password.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar la contraseña');
        return res;
    },

    // Activar/desactivar un ajuste de la aplicación
    async actualizar_activacion(clave, valor) {
        const res = await Http.post(this.baseUrl + 'actualizar_activacion.php', { clave: clave, valor: valor }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar el ajuste');
        return res;
    }
};
