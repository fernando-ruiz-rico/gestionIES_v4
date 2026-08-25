// API client para el módulo de Materias

const MateriasAPI = {
    baseUrl: '../backend/api/materias/',

    // `idCurso` (opcional): filtra las materias del curso; sin él, todas (fiel a v3)
    async listar(idCurso) {
        const url = this.baseUrl + 'listar.php' + ((idCurso > 0) ? `?idCurso=${idCurso}` : '');
        const data = await Http.get(url, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: [] };
    },

    async obtener(id) {
        const data = await Http.get(this.baseUrl + `obtener.php?id=${id}`, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    async guardar(materia) {
        return Http.post(this.baseUrl + 'guardar.php', materia, 'include');
    },

    async eliminar(id) {
        return Http.post(this.baseUrl + 'eliminar.php', { id: id }, 'include');
    },

    // Datos de una materia por grupo (fiel a v3: cargar_forms_materias_grupos.php)
    async listar_materias_grupos(idMateria, idCurso) {
        const url = this.baseUrl + `listar_materias_grupos.php?idMateria=${idMateria}&idCurso=${idCurso}`;
        const data = await Http.get(url, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    // Inserta/modifica los datos de una materia para un grupo (fiel a v3)
    async insertar_materia_grupo(datos) {
        return Http.post(this.baseUrl + 'insertar_materia_grupo.php', datos, 'include');
    },

    // Lista de competencias asociadas a una materia + opciones del ciclo
    async competencias_listar(idMateria) {
        const url = this.baseUrl + `competencias_listar.php?idMateria=${idMateria}`;
        const data = await Http.get(url, 'include');
        return data.success
            ? { success: true, data: data.data }
            : { success: false, error: data.error || 'Error de conexión', data: null };
    },

    // Asocia una competencia a una materia (solo admin, fiel a v3)
    async competencias_asociar(idMateria, idCompetencia) {
        return Http.post(this.baseUrl + 'competencias_asociar.php', { idMateria: idMateria, idCompetencia: idCompetencia }, 'include');
    },

    // Desasocia una competencia de una materia (solo admin, fiel a v3)
    async competencias_borrar(idMateria, idCompetencia) {
        return Http.post(this.baseUrl + 'competencias_borrar.php', { idMateria: idMateria, idCompetencia: idCompetencia }, 'include');
    }
};
