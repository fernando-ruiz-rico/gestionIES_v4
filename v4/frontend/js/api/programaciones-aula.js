const programacionesAulaAPI = {
    baseUrl: '../backend/api/programaciones_aula/',

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

    async cargarTemas(idMateria) {
        const response = await fetch(`${this.baseUrl}temas.php?idMateria=${idMateria}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar temas');
        }
        return data.data;
    },

    async cargarContenido(idTema, idGrupo, idProfesor) {
        let url = `${this.baseUrl}contenido.php?idTema=${idTema}&idGrupo=${idGrupo}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el contenido');
        }
        return data.data;
    },

    async guardar(idTema, idGrupo, idProfesor, texto) {
        const response = await fetch(this.baseUrl + 'guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idTema,
                idGrupo,
                idProfesor,
                texto
            })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar el contenido');
        }
        return data;
    }
};
