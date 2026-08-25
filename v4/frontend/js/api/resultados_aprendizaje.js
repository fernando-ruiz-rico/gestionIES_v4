// API client para el módulo de Resultados de Aprendizaje (Fase 4.1)

const ResultadosArendizajeAPI = {
    baseUrl: '../backend/api/resultados_aprendizaje.php',

    // Lista las materias del selector. El departamento del admin se pasa
    // como parámetro; el jefe y el profesor usan el de su sesión (como en v3).
    async listar_materias(idDepartamento) {
        const url = this.baseUrl + '?action=listar_materias' +
            (idDepartamento ? '&idDepartamento=' + idDepartamento : '');
        const data = await Http.get(url, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    },

    async cargar(idMateria) {
        const data = await Http.get(this.baseUrl + '?action=cargar&idMateria=' + idMateria, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    },

    async guardar(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async actualizar_horas(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_horas', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async actualizar_evaluacion(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_evaluacion', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async eliminar(id) {
        const res = await Http.post(this.baseUrl + '?action=eliminar', { id: id }, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async obtener(id) {
        const data = await Http.get(this.baseUrl + '?action=obtener&id=' + id, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    },

    async guardar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=guardar_criterio', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async actualizar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=actualizar_criterio', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async eliminar_criterio(data) {
        const res = await Http.post(this.baseUrl + '?action=eliminar_criterio', data, 'include');
        return res.success
            ? { success: true, data: res.data }
            : { success: false, error: res.error || 'Error de conexión' };
    },

    async cargar_criterios(idResultado) {
        const data = await Http.get(this.baseUrl + '?action=cargar_criterios&idResultado=' + idResultado, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión' };
    }
};
