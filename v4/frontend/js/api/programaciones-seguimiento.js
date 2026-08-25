const programacionesSeguimientoAPI = {
    baseUrl: '../backend/api/programaciones_seguimiento/',

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

    cargarEvaluaciones() {
        return Http.getOk(this.baseUrl + 'evaluaciones.php', 'Error al cargar las evaluaciones');
    },

    cargar(idMateria, idGrupo, idEvaluacion, idProfesor) {
        let url = `${this.baseUrl}cargar.php?idMateria=${idMateria}&idGrupo=${idGrupo}&idEvaluacion=${idEvaluacion}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar el seguimiento');
    },

    async guardar(payload) {
        const data = await Http.post(this.baseUrl + 'guardar.php', payload);
        if (!data.success) throw new Error(data.error || 'Error al guardar el seguimiento');
        return data;
    }
};
