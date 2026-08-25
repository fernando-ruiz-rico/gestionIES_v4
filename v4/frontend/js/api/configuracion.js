// API client para el módulo de Configuración (Fase 7.3)

const ConfiguracionAPI = {
    baseUrl: '../backend/api/configuracion.php',

    obtener() {
        return Http.get(this.baseUrl + '?action=obtener', 'include');
    },

    actualizar_password(data) {
        return Http.post(this.baseUrl + '?action=actualizar_password', data, 'include');
    },

    actualizar_activacion(clave, valor) {
        return Http.post(this.baseUrl + '?action=actualizar_activacion', { clave: clave, valor: valor }, 'include');
    }
};
