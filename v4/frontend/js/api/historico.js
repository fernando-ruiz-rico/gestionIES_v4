// API client para el módulo de Histórico de selecciones (Fase 7.1)

const HistoricoAPI = {
    baseUrl: '../backend/api/historico.php',

    async listar(idDepartamento, idEscenario) {
        const data = await Http.get(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error desconocido' };
    }
};

const EstadisticasAPI = {
    baseUrl: '../backend/api/estadisticas.php',

    async listar(idDepartamento, idEscenario) {
        const data = await Http.get(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error desconocido' };
    }
};
