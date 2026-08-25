// API client para el módulo de Ciclos

const CiclosAPI = {
    baseUrl: '../backend/api/ciclos/',

    async listar() {
        const data = await Http.get(this.baseUrl + 'listar.php', 'include');
        return data.success
            ? { success: true, data: Array.isArray(data.data) ? data.data : [] }
            : { success: false, error: data.error || 'Error de conexión', data: [] };
    },

    async obtener(id) {
        const data = await Http.get(this.baseUrl + `obtener.php?id=${id}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    async guardar(ciclo) {
        return Http.post(this.baseUrl + 'guardar.php', ciclo, 'include');
    },

    async eliminar(id) {
        return Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
    },

    // Asociaciones de cursos con el ciclo

    async asociacionesCursos(idCiclo) {
        const data = await Http.get(this.baseUrl + `asociaciones_cursos.php?idCiclo=${idCiclo}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    async guardarAsociacionCurso(asociacion) {
        return Http.post(this.baseUrl + 'guardar_asociacion_curso.php', asociacion, 'include');
    },

    async borrarAsociacionCurso(idCiclo, idCurso) {
        return Http.post(this.baseUrl + 'borrar_asociacion_curso.php', { idCiclo: idCiclo, idCurso: idCurso }, 'include');
    },

    // Asociaciones de unidades de competencia con el ciclo

    async asociacionesUnidades(idCiclo) {
        const data = await Http.get(this.baseUrl + `asociaciones_unidades.php?idCiclo=${idCiclo}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    async guardarAsociacionUnidad(idCiclo, codigoUnidad) {
        return Http.post(this.baseUrl + 'guardar_asociacion_unidad.php', { idCiclo: idCiclo, codigoUnidad: codigoUnidad }, 'include');
    },

    async borrarAsociacionUnidad(idCiclo, codigoUnidad) {
        return Http.post(this.baseUrl + 'borrar_asociacion_unidad.php', { idCiclo: idCiclo, codigoUnidad: codigoUnidad }, 'include');
    }
};
