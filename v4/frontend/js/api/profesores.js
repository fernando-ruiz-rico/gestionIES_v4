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

    // Preferencias horarias (rejilla de la opción «Perfil» y del modal del
    // módulo «Profesores»): horas de la tabla "horas" (manana/tarde) +
    // cadenas de códigos R/A (día + hora con '_', p. ej. L07_55).
    // Sin idProfesor, el endpoint devuelve las del propio profesor de la
    // sesión (o las horas vacías si es admin).
    preferencias(idProfesor) {
        const url = (idProfesor === undefined || idProfesor === null || idProfesor === '')
            ? '../backend/api/profesores/preferencias.php'
            : `../backend/api/profesores/preferencias.php?idProfesor=${idProfesor}`;
        return Http.getOk(url, 'Error al cargar las preferencias horarias', 'include');
    },

    // Guardar profesor (crear o actualizar) — el cuerpo es un objeto plano (JSON)
    async guardar(datos) {
        const data = await Http.post('../backend/api/profesores/guardar.php', datos, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar profesor');
        return data;
    },

    // Eliminar profesor
    async eliminar(id) {
        const data = await Http.post('../backend/api/profesores/eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar profesor');
        return data;
    },

    // Actualizar jefe de departamento
    async actualizarJefe(idProfesor, idDepartamento) {
        const data = await Http.post('../backend/api/profesores/actualizar_jefe.php', { idProfesor: idProfesor, idDepartamento: idDepartamento }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al actualizar jefe de departamento');
        return data;
    },

    // Activar/desactivar profesor
    async actualizarActivo(idProfesor) {
        const data = await Http.post('../backend/api/profesores/actualizar_activo.php', { idProfesor: idProfesor }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al actualizar el estado del profesor');
        return data;
    },

    // Ordenar profesores
    async ordenar(orden) {
        const data = await Http.post('../backend/api/profesores/ordenar.php', { orden: orden }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al ordenar profesores');
        return data;
    }
};
