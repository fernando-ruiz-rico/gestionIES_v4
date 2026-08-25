// API client para el módulo de Ciclos
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const CiclosAPI = {
    baseUrl: '../backend/api/ciclos/',

    // Listar ciclos
    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al cargar los ciclos', 'include');
    },

    // Obtener un ciclo
    obtener(id) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${id}`, 'Error al cargar el ciclo', 'include');
    },

    // Guardar ciclo (crear o editar)
    async guardar(ciclo) {
        const data = await Http.post(this.baseUrl + 'guardar.php', ciclo, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar el ciclo');
        return data;
    },

    // Eliminar ciclo
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar el ciclo');
        return data;
    },

    // Asociaciones de cursos con el ciclo

    // Listar las asociaciones de cursos
    asociacionesCursos(idCiclo) {
        return Http.getOk(this.baseUrl + `asociaciones_cursos.php?idCiclo=${idCiclo}`, 'Error al cargar las asociaciones de cursos', 'include');
    },

    // Guardar una asociación de curso
    async guardarAsociacionCurso(asociacion) {
        const data = await Http.post(this.baseUrl + 'guardar_asociacion_curso.php', asociacion, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar la asociación');
        return data;
    },

    // Borrar una asociación de curso
    async borrarAsociacionCurso(idCiclo, idCurso) {
        const data = await Http.post(this.baseUrl + 'borrar_asociacion_curso.php', { idCiclo: idCiclo, idCurso: idCurso }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al borrar la asociación');
        return data;
    },

    // Asociaciones de unidades de competencia con el ciclo

    // Listar las asociaciones de unidades
    asociacionesUnidades(idCiclo) {
        return Http.getOk(this.baseUrl + `asociaciones_unidades.php?idCiclo=${idCiclo}`, 'Error al cargar las asociaciones de unidades', 'include');
    },

    // Guardar una asociación de unidad
    async guardarAsociacionUnidad(idCiclo, codigoUnidad) {
        const data = await Http.post(this.baseUrl + 'guardar_asociacion_unidad.php', { idCiclo: idCiclo, codigoUnidad: codigoUnidad }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar la asociación');
        return data;
    },

    // Borrar una asociación de unidad
    async borrarAsociacionUnidad(idCiclo, codigoUnidad) {
        const data = await Http.post(this.baseUrl + 'borrar_asociacion_unidad.php', { idCiclo: idCiclo, codigoUnidad: codigoUnidad }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al borrar la asociación');
        return data;
    }
};
