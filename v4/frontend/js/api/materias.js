// API client para el módulo de Materias
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const MateriasAPI = {
    baseUrl: '../backend/api/materias/',

    // `idCurso` (opcional): filtra las materias del curso; sin él, todas (fiel a v3)
    listar(idCurso) {
        const url = this.baseUrl + 'listar.php' + ((idCurso > 0) ? `?idCurso=${idCurso}` : '');
        return Http.getOk(url, 'Error al cargar las materias', 'include');
    },

    // Obtener una materia
    obtener(id) {
        return Http.getOk(this.baseUrl + `obtener.php?id=${id}`, 'Error al cargar la materia', 'include');
    },

    // Guardar materia (crear o editar)
    async guardar(materia) {
        const data = await Http.post(this.baseUrl + 'guardar.php', materia, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar la materia');
        return data;
    },

    // Eliminar materia
    async eliminar(id) {
        const data = await Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al eliminar la materia');
        return data;
    },

    // Datos de una materia por grupo (fiel a v3: cargar_forms_materias_grupos.php)
    listar_materias_grupos(idMateria, idCurso) {
        const url = this.baseUrl + `listar_materias_grupos.php?idMateria=${idMateria}&idCurso=${idCurso}`;
        return Http.getOk(url, 'Error al cargar los datos por grupo', 'include');
    },

    // Inserta/modifica los datos de una materia para un grupo (fiel a v3)
    async insertar_materia_grupo(datos) {
        const data = await Http.post(this.baseUrl + 'insertar_materia_grupo.php', datos, 'include');
        if (!data.success) throw new Error(data.error || 'Error al guardar los datos del grupo');
        return data;
    },

    // Lista de competencias asociadas a una materia + opciones del ciclo
    competencias_listar(idMateria) {
        const url = this.baseUrl + `competencias_listar.php?idMateria=${idMateria}`;
        return Http.getOk(url, 'Error al cargar las competencias', 'include');
    },

    // Asocia una competencia a una materia (solo admin, fiel a v3)
    async competencias_asociar(idMateria, idCompetencia) {
        const data = await Http.post(this.baseUrl + 'competencias_asociar.php', { idMateria: idMateria, idCompetencia: idCompetencia }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al asociar la competencia');
        return data;
    },

    // Desasocia una competencia de una materia (solo admin, fiel a v3)
    async competencias_borrar(idMateria, idCompetencia) {
        const data = await Http.post(this.baseUrl + 'competencias_borrar.php', { idMateria: idMateria, idCompetencia: idCompetencia }, 'include');
        if (!data.success) throw new Error(data.error || 'Error al desvincular la competencia');
        return data;
    }
};
