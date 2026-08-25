// API client para el módulo de Histórico de selecciones (Fase 7.1)
// y para el módulo de Estadísticas de la selección.
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo.
const HistoricoAPI = {
    baseUrl: '../backend/api/historico.php',

    // Listar el histórico de un departamento y escenario
    listar(idDepartamento, idEscenario) {
        return Http.getOk(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'Error al cargar el histórico', 'include');
    }
};

const EstadisticasAPI = {
    baseUrl: '../backend/api/estadisticas.php',

    // Listar las estadísticas de un departamento y escenario
    listar(idDepartamento, idEscenario) {
        return Http.getOk(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'Error al cargar las estadísticas', 'include');
    }
};
