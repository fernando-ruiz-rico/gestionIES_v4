// API de profesores para comunicación con el backend
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ProfesoresAPI = {
    // Listar profesores de un departamento
    listar(idDepartamento) {
        return Http.getOk(`../backend/api/profesores/listar.php?idDepartamento=${idDepartamento}`, 'Error al cargar profesores', 'include');
    },

    // Obtener un profesor por ID
    obtener(id) {
        return Http.getOk(`../backend/api/profesores/obtener.php?id=${id}`, 'Error al cargar profesor', 'include');
    },

    // Guardar profesor (crear o actualizar)
    async guardar(formData) {
        const data = await Http.post('../backend/api/profesores/guardar.php', formData, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar profesor');
        return data;
    },

    // Eliminar profesor
    async eliminar(id) {
        const formData = new FormData();
        formData.append('id', id);
        const data = await Http.post('../backend/api/profesores/eliminar.php', formData, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar profesor');
        return data;
    },

    // Actualizar jefe de departamento
    async actualizarJefe(idProfesor, idDepartamento) {
        const formData = new FormData();
        formData.append('idProfesor', idProfesor);
        formData.append('idDepartamento', idDepartamento);
        const data = await Http.post('../backend/api/profesores/actualizar_jefe.php', formData, 'include');
        if (!data.success) throw new Error(data.error || 'Error al actualizar jefe de departamento');
        return data;
    },

    // Activar/desactivar profesor
    async actualizarActivo(idProfesor) {
        const formData = new FormData();
        formData.append('idProfesor', idProfesor);
        const data = await Http.post('../backend/api/profesores/actualizar_activo.php', formData, 'include');
        if (!data.success) throw new Error(data.error || 'Error al actualizar el estado del profesor');
        return data;
    },

    // Ordenar profesores
    async ordenar(orden) {
        const formData = new FormData();
        formData.append('orden', orden);
        const data = await Http.post('../backend/api/profesores/ordenar.php', formData, 'include');
        if (!data.success) throw new Error(data.error || 'Error al ordenar profesores');
        return data;
    }
};
