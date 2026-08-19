const CiclosAPI = {
    baseUrl: '../backend/api/ciclos/',
    async listar() {
        try { const r = await fetch(this.baseUrl + 'listar.php', { credentials: 'include' }); return await r.json(); }
        catch (e) { console.error(e); return []; }
    },
    async obtener(id) {
        try { const r = await fetch(this.baseUrl + `obtener.php?id=${id}`, { credentials: 'include' }); return await r.json(); }
        catch (e) { return null; }
    },
    async guardar(ciclo) {
        try { const r = await fetch(this.baseUrl + 'guardar.php', { method: 'POST', credentials: 'include', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(ciclo) }); return await r.json(); }
        catch (e) { return { success: false, error: 'Error' }; }
    },
    async eliminar(id) {
        try { const r = await fetch(this.baseUrl + 'eliminar.php', { method: 'POST', credentials: 'include', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ idCiclo: id }) }); return await r.json(); }
        catch (e) { return { success: false, error: 'Error' }; }
    }
};
