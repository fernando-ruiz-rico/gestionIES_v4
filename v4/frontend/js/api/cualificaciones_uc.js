// API client para el módulo de Cualificaciones y Unidades de Competencia (Fase 4.3)

const CualificacionesUCAPI = {
    baseUrl: '../backend/api/cualificaciones_uc.php',

    async listar_cualificaciones() {
        const res = await fetch(this.baseUrl + '?action=listar_cualificaciones', { credentials: 'include' });
        return res.json();
    },

    async obtener_cualificacion(codigo) {
        const res = await fetch(this.baseUrl + '?action=obtener_cualificacion&codigo=' + encodeURIComponent(codigo), { credentials: 'include' });
        return res.json();
    },

    async guardar_cualificacion(data) {
        const res = await fetch(this.baseUrl + '?action=guardar_cualificacion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },

    async eliminar_cualificacion(codigo) {
        const res = await fetch(this.baseUrl + '?action=eliminar_cualificacion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: codigo })
        });
        return res.json();
    },

    async listar_unidades() {
        const res = await fetch(this.baseUrl + '?action=listar_unidades', { credentials: 'include' });
        return res.json();
    },

    async obtener_unidad(codigo) {
        const res = await fetch(this.baseUrl + '?action=obtener_unidad&codigo=' + encodeURIComponent(codigo), { credentials: 'include' });
        return res.json();
    },

    async guardar_unidad(data) {
        const res = await fetch(this.baseUrl + '?action=guardar_unidad', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },

    async eliminar_unidad(codigo) {
        const res = await fetch(this.baseUrl + '?action=eliminar_unidad', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: codigo })
        });
        return res.json();
    },

    async listar_asociaciones(codigo) {
        const res = await fetch(this.baseUrl + '?action=listar_asociaciones&codigo=' + encodeURIComponent(codigo), { credentials: 'include' });
        return res.json();
    },

    async guardar_asociacion(data) {
        const res = await fetch(this.baseUrl + '?action=guardar_asociacion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },

    async eliminar_asociacion(data) {
        const res = await fetch(this.baseUrl + '?action=eliminar_asociacion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    }
};
