// API client para el módulo de Selección de materias de Desideratas

const SeleccionAPI = {
    baseUrl: '../backend/api/seleccion.php',

    async listar_escenarios(idDepartamento) {
        const res = await fetch(this.baseUrl + '?action=listar_escenarios&idDepartamento=' + idDepartamento, { credentials: 'include' });
        return res.json();
    },

    async listar_especialidades(idDepartamento) {
        const res = await fetch(this.baseUrl + '?action=listar_especialidades&idDepartamento=' + idDepartamento, { credentials: 'include' });
        return res.json();
    },

    async listar_profesores(idDepartamento, idEspecialidad, idEscenario) {
        let params = 'idDepartamento=' + idDepartamento;
        if (idEspecialidad) params += '&idEspecialidad=' + encodeURIComponent(idEspecialidad);
        const res = await fetch(this.baseUrl + '?action=listar_profesores&idEscenario=' + idEscenario + '&' + params, { credentials: 'include' });
        return res.json();
    },

    async listar_cursos(idDepartamento, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar_cursos&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    },

    async listar_seleccion(idProfesor, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar_seleccion&idProfesor=' + idProfesor + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    },

    // Nombres de los profesores que ya eligieron una materia
    async listar_profesores_materia(idMateria, idGrupo, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar_profesores_materia&idMateria=' + idMateria + '&idGrupo=' + idGrupo + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    },

    async insertar_seleccion(data) {
        const res = await fetch(this.baseUrl + '?action=insertar_seleccion&idEscenario=' + data.idEscenario, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idProfesor: data.idProfesor,
                idMateria: data.idMateria,
                idGrupo: data.idGrupo,
                horas: data.horas,
                idEscenario: data.idEscenario
            })
        });
        return res.json();
    },

    async borrar_seleccion(id) {
        const res = await fetch(this.baseUrl + '?action=borrar_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        return res.json();
    },

    async borrar_toda_seleccion(idProfesor, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=borrar_toda_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idProfesor: idProfesor, idEscenario: idEscenario })
        });
        return res.json();
    },

    // Vacía todas las selecciones del escenario (solo jefe de departamento o admin)
    async borrar_todas_selecciones(idEscenario) {
        const res = await fetch(this.baseUrl + '?action=borrar_todas_selecciones&idEscenario=' + idEscenario, {
            method: 'POST',
            credentials: 'include'
        });
        return res.json();
    },

    // Reordena las selecciones del profesor; "ids" son los ids de la selección en el orden nuevo
    async ordenar_seleccion(ids, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=ordenar_seleccion&idEscenario=' + idEscenario, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        });
        return res.json();
    }
};
