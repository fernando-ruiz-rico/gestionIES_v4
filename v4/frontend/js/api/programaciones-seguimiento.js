const programacionesSeguimientoAPI = {
    baseUrl: '../backend/api/programaciones_seguimiento/',

    async cargarProfesores() {
        const response = await fetch(this.baseUrl + 'profesores.php');
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los profesores');
        }
        return data.data;
    },

    async cargarMaterias(idProfesor) {
        let url = this.baseUrl + 'materias.php';
        if (idProfesor > 0) {
            url += `?idProfesor=${idProfesor}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar materias');
        }
        return data.data;
    },

    async cargarGrupos(idMateria, idProfesor) {
        let url = `${this.baseUrl}grupos.php?idMateria=${idMateria}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar grupos');
        }
        return data.data;
    },

    async cargarEvaluaciones() {
        const response = await fetch(this.baseUrl + 'evaluaciones.php');
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar las evaluaciones');
        }
        return data.data;
    },

    async cargar(idMateria, idGrupo, idEvaluacion, idProfesor) {
        let url = `${this.baseUrl}cargar.php?idMateria=${idMateria}&idGrupo=${idGrupo}&idEvaluacion=${idEvaluacion}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el seguimiento');
        }
        return data.data;
    },

    async guardar(payload) {
        const response = await fetch(this.baseUrl + 'guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar el seguimiento');
        }
        return data;
    }
};
