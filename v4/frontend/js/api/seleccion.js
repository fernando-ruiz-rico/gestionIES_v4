// API client para el módulo de Selección de materias de Desideratas

const SeleccionAPI = {
    baseUrl: '../backend/api/seleccion.php',

    listar_escenarios(idDepartamento) {
        return Http.get(this.baseUrl + '?action=listar_escenarios&idDepartamento=' + idDepartamento, 'include');
    },

    listar_especialidades(idDepartamento) {
        return Http.get(this.baseUrl + '?action=listar_especialidades&idDepartamento=' + idDepartamento, 'include');
    },

    listar_profesores(idDepartamento, idEspecialidad, idEscenario) {
        let params = 'idDepartamento=' + idDepartamento;
        if (idEspecialidad) params += '&idEspecialidad=' + encodeURIComponent(idEspecialidad);
        return Http.get(this.baseUrl + '?action=listar_profesores&idEscenario=' + idEscenario + '&' + params, 'include');
    },

    listar_cursos(idDepartamento, idEscenario) {
        return Http.get(this.baseUrl + '?action=listar_cursos&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'include');
    },

    listar_seleccion(idProfesor, idEscenario) {
        return Http.get(this.baseUrl + '?action=listar_seleccion&idProfesor=' + idProfesor + '&idEscenario=' + idEscenario, 'include');
    },

    // Nombres de los profesores que ya eligieron una materia
    listar_profesores_materia(idMateria, idGrupo, idEscenario) {
        return Http.get(this.baseUrl + '?action=listar_profesores_materia&idMateria=' + idMateria + '&idGrupo=' + idGrupo + '&idEscenario=' + idEscenario, 'include');
    },

    insertar_seleccion(data) {
        return Http.post(this.baseUrl + '?action=insertar_seleccion&idEscenario=' + data.idEscenario, {
            idProfesor: data.idProfesor,
            idMateria: data.idMateria,
            idGrupo: data.idGrupo,
            horas: data.horas,
            idEscenario: data.idEscenario
        }, 'include');
    },

    borrar_seleccion(id) {
        return Http.post(this.baseUrl + '?action=borrar_seleccion', { id: id }, 'include');
    },

    borrar_toda_seleccion(idProfesor, idEscenario) {
        return Http.post(this.baseUrl + '?action=borrar_toda_seleccion', { idProfesor: idProfesor, idEscenario: idEscenario }, 'include');
    },

    // Vacía todas las selecciones del escenario (solo jefe de departamento o admin)
    borrar_todas_selecciones(idEscenario) {
        return Http.post(this.baseUrl + '?action=borrar_todas_selecciones&idEscenario=' + idEscenario, null, 'include');
    },

    // Reordena las selecciones del profesor; "ids" son los ids de la selección en el orden nuevo
    ordenar_seleccion(ids, idEscenario) {
        return Http.post(this.baseUrl + '?action=ordenar_seleccion&idEscenario=' + idEscenario, { ids: ids }, 'include');
    }
};
