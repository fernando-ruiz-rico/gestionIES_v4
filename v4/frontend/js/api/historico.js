// API client para el módulo de Histórico de selecciones (Fase 7.1)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo.
const HistoricoAPI = {
    baseUrl: '../backend/api/historico/',

    // Listar el histórico de un departamento y escenario
    listar(idDepartamento, idEscenario) {
        return Http.getOk(this.baseUrl + 'listar.php?idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'Error al cargar el histórico', 'include');
    }
};
