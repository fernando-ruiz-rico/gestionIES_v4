// API para los contenidos por defecto de los temas / unidades (Fase 2.7)
// Backend por fichero: backend/api/temas_contenidos_defecto/
const TemasContenidosDefectoAPI = {
    baseUrl: '../backend/api/temas_contenidos_defecto/',

    // Carga los contenidos por defecto de un departamento (contexto, recursos,
    // metodología y acciones)
    cargar(idDepartamento) {
        return Http.getOk(`${this.baseUrl}cargar.php?idDepartamento=${idDepartamento}`, 'Error al cargar contenidos por defecto');
    },

    // Guarda (inserta o actualiza) los contenidos por defecto de un departamento
    guardar(data) {
        return Http.postOk(`${this.baseUrl}guardar.php`, data, 'Error al guardar contenidos por defecto');
    }
};
