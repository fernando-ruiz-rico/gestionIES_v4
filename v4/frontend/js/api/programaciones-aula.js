const ProgramacionesAulaAPI = {
    baseUrl: '../backend/api/programaciones_aula/',

    cargarProfesores() {
        return Http.getOk(this.baseUrl + 'profesores.php', 'Error al cargar los profesores');
    },

    cargarMaterias(idProfesor) {
        let url = this.baseUrl + 'materias.php';
        if (idProfesor > 0) {
            url += `?idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar materias');
    },

    cargarGrupos(idMateria, idProfesor) {
        let url = `${this.baseUrl}grupos.php?idMateria=${idMateria}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar grupos');
    },

    cargarTemas(idMateria) {
        return Http.getOk(`${this.baseUrl}temas.php?idMateria=${idMateria}`, 'Error al cargar temas');
    },

    cargarContenido(idTema, idGrupo, idProfesor) {
        let url = `${this.baseUrl}contenido.php?idTema=${idTema}&idGrupo=${idGrupo}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar el contenido');
    },

    async guardar(idTema, idGrupo, idProfesor, texto) {
        const data = await Http.post(this.baseUrl + 'guardar.php', { idTema, idGrupo, idProfesor, texto });
        if (!data.success) throw new Error(data.error || 'Error al guardar el contenido');
        return data;
    }
};
