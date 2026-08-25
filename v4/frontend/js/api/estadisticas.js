// API client para el módulo de Estadísticas de la selección
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo.
const EstadisticasAPI = {
    baseUrl: '../backend/api/estadisticas/',

    // Listar las estadísticas de un departamento y escenario
    listar(idDepartamento, idEscenario) {
        return Http.getOk(this.baseUrl + 'listar.php?idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'Error al cargar las estadísticas', 'include');
    }
};
