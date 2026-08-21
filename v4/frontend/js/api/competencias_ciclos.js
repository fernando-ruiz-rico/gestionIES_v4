// API client para el módulo de Competencias por Ciclo (Fase 4.2)

const CompetenciasCiclosAPI = {
    baseUrl: '../backend/api/competencias_ciclos.php',

    async listar_ciclos() {
        const res = await fetch(this.baseUrl + '?action=listar_ciclos', { credentials: 'include' });
        return res.json();
    },

    async listar(idCiclo) {
        const res = await fetch(this.baseUrl + '?action=listar&idCiclo=' + idCiclo, { credentials: 'include' });
        return res.json();
    },

    async obtener(id) {
        const res = await fetch(this.baseUrl + '?action=obtener&id=' + id, { credentials: 'include' });
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
    },

    async ordenar(orden) {
        const res = await fetch(this.baseUrl + '?action=ordenar', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orden: orden })
        });
        return res.json();
    },

    async eliminar(id) {
        const res = await fetch(this.baseUrl + '?action=eliminar', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        return res.json();
    }
};
