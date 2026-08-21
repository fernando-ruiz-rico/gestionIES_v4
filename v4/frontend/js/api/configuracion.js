// API client para el módulo de Configuración (Fase 7.3)

const ConfiguracionAPI = {
    baseUrl: '../backend/api/configuracion.php',

    async obtener() {
        const res = await fetch(this.baseUrl + '?action=obtener', { credentials: 'include' });
        return res.json();
    },

    async actualizar_password(data) {
        const res = await fetch(this.baseUrl + '?action=actualizar_password', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },

    async actualizar_activacion(clave, valor) {
        const res = await fetch(this.baseUrl + '?action=actualizar_activacion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ clave: clave, valor: valor })
        });
        return res.json();
    }
};
