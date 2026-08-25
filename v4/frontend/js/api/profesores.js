// API de profesores para comunicación con el backend

const ProfesoresAPI = {
    // Listar profesores de un departamento
    async listar(idDepartamento) {
        const data = await Http.get(`../backend/api/profesores/listar.php?idDepartamento=${idDepartamento}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al cargar profesores' };
    },

    // Obtener un profesor por ID
    async obtener(id) {
        const data = await Http.get(`../backend/api/profesores/obtener.php?id=${id}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al cargar profesor' };
    },

    // Guardar profesor (crear o actualizar)
    async guardar(formData) {
        const data = await Http.post('../backend/api/profesores/guardar.php', formData, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al guardar profesor' };
    },

    // Eliminar profesor
    async eliminar(id) {
        const formData = new FormData();
        formData.append('id', id);
        const data = await Http.post('../backend/api/profesores/eliminar.php', formData, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al eliminar profesor' };
    },

    // Actualizar jefe de departamento
    async actualizarJefe(idProfesor, idDepartamento) {
        const formData = new FormData();
        formData.append('idProfesor', idProfesor);
        formData.append('idDepartamento', idDepartamento);
        const data = await Http.post('../backend/api/profesores/actualizar_jefe.php', formData, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al actualizar jefe de departamento' };
    },

    // Activar/desactivar profesor
    async actualizarActivo(idProfesor) {
        const formData = new FormData();
        formData.append('idProfesor', idProfesor);
        const data = await Http.post('../backend/api/profesores/actualizar_activo.php', formData, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al actualizar estado del profesor' };
    },

    // Ordenar profesores
    async ordenar(orden) {
        const formData = new FormData();
        formData.append('orden', orden);
        const data = await Http.post('../backend/api/profesores/ordenar.php', formData, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error al ordenar profesores' };
    }
};
