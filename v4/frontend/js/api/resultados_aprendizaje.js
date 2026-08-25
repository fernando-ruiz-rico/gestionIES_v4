// API client para el módulo de Resultados de Aprendizaje (Fase 4.1)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ResultadosArendizajeAPI = {
    baseUrl: '../backend/api/resultados_aprendizaje.php',

    // Lista las materias del selector. El departamento del admin se pasa
    // como parámetro; el jefe y el profesor usan el de su sesión (como en v3).
    listar_materias(idDepartamento) {
        const url = this.baseUrl + '?action=listar_materias' +
            (idDepartamento ? '&idDepartamento=' + idDepartamento : '');
        return Http.getOk(url, 'Error al cargar las materias', 'include');
    },

    // Cargar los resultados de una materia
    cargar(idMateria) {
        return Http.getOk(this.baseUrl + '?action=cargar&idMateria=' + idMateria, 'Error al cargar los resultados de aprendizaje', 'include');
    },

    // Guardar resultado (crear o actualizar)
    async guardar(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar el resultado');
        return res;
    },

    // Actualizar las horas de empresa
    async actualizar_horas(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_horas', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar las horas de empresa');
        return res;
    },

    // Actualizar los datos de evaluación de un resultado
    async actualizar_evaluacion(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_evaluacion', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar la evaluación');
        return res;
    },

    // Eliminar resultado
    async eliminar(id) {
        const res = await Http.post(this.baseUrl + '?action=eliminar', { id: id }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar el resultado');
        return res;
    },

    // Obtener un resultado
    obtener(id) {
        return Http.getOk(this.baseUrl + '?action=obtener&id=' + id, 'Error al cargar el resultado', 'include');
    },

    // Guardar un criterio de evaluación
    async guardar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar_criterio', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar el criterio');
        return res;
    },

    // Actualizar un criterio de evaluación
    async actualizar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_criterio', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar el criterio');
        return res;
    },

    // Eliminar un criterio de evaluación
    async eliminar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=eliminar_criterio', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar el criterio');
        return res;
    },

    // Cargar los criterios de un resultado
    cargar_criterios(idResultado) {
        return Http.getOk(this.baseUrl + '?action=cargar_criterios&idResultado=' + idResultado, 'Error al cargar los criterios', 'include');
    }
};
