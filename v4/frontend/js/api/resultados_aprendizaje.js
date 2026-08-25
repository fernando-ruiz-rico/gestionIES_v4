// API client para el módulo de Resultados de Aprendizaje (Fase 4.1)
//
// Habla con ../backend/api/resultados_aprendizaje/ (un fichero por acción).
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ResultadosAprendizajeAPI = {
    baseUrl: '../backend/api/resultados_aprendizaje/',

    // Lista las materias del selector. El departamento del admin se pasa
    // como parámetro; el jefe y el profesor usan el de su sesión (como en v3).
    listar_materias(idDepartamento) {
        const url = this.baseUrl + 'listar_materias.php' +
            (idDepartamento ? '?idDepartamento=' + idDepartamento : '');
        return Http.getOk(url, 'Error al cargar las materias', 'include');
    },

    // Cargar los resultados de una materia
    cargar(idMateria) {
        return Http.getOk(this.baseUrl + 'cargar.php?idMateria=' + idMateria, 'Error al cargar los resultados de aprendizaje', 'include');
    },

    // Guardar resultado (crear o actualizar)
    async guardar(data) {
        const res = await Http.post(this.baseUrl + 'guardar.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar el resultado');
        return res;
    },

    // Actualizar las horas de empresa
    async actualizar_horas(data) {
        const res = await Http.post(this.baseUrl + 'actualizar_horas.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar las horas de empresa');
        return res;
    },

    // Actualizar los datos de evaluación de un resultado
    async actualizar_evaluacion(data) {
        const res = await Http.post(this.baseUrl + 'actualizar_evaluacion.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar la evaluación');
        return res;
    },

    // Eliminar resultado
    async eliminar(id) {
        const res = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar el resultado');
        return res;
    },

    // Obtener un resultado
    obtener(id) {
        return Http.getOk(this.baseUrl + 'obtener.php?id=' + id, 'Error al cargar el resultado', 'include');
    },

    // Guardar un criterio de evaluación
    async guardar_criterio(data) {
        const res = await Http.post(this.baseUrl + 'guardar_criterio.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar el criterio');
        return res;
    },

    // Actualizar un criterio de evaluación
    async actualizar_criterio(data) {
        const res = await Http.post(this.baseUrl + 'actualizar_criterio.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al actualizar el criterio');
        return res;
    },

    // Eliminar un criterio de evaluación
    async eliminar_criterio(data) {
        const res = await Http.post(this.baseUrl + 'eliminar_criterio.php', data, 'include');
        if (!res.success) throw new Error(res.error || 'Error al eliminar el criterio');
        return res;
    },

    // Cargar los criterios de un resultado
    cargar_criterios(idResultado) {
        return Http.getOk(this.baseUrl + 'cargar_criterios.php?idResultado=' + idResultado, 'Error al cargar los criterios', 'include');
    }
};
