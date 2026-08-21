// API client para el módulo de Histórico de selecciones (Fase 7.1)

const HistoricoAPI = {
    baseUrl: '../backend/api/historico.php',

    async listar(idDepartamento, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    }
};

const EstadisticasAPI = {
    baseUrl: '../backend/api/estadisticas.php',

    async listar(idDepartamento, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    }
};
