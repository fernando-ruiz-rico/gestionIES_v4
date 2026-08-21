// API client para el módulo de Actas de departamentos (Fase 6.1)

const ActasAPI = {
    baseUrl: '../backend/api/actas.php',

    async listar(idDepartamento) {
        const res = await fetch(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento, { credentials: 'include' });
        return res.json();
    },

    async obtener(idActa) {
        const res = await fetch(this.baseUrl + '?action=obtener&idActa=' + idActa, { credentials: 'include' });
        return res.json();
    },

    async guardar(data) {
        const res = await fetch(this.baseUrl + '?action=guardar', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    }
};
