// API client para el módulo de Selección de materias de Desideratas
//
// Habla con ../backend/api/seleccion/ (un fichero por acción).
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const SeleccionAPI = {
    baseUrl: '../backend/api/seleccion/',

    // Listar escenarios de un departamento
    listar_escenarios(idDepartamento) {
        return Http.getOk(this.baseUrl + 'listar_escenarios.php?idDepartamento=' + idDepartamento, 'Error al cargar los escenarios', 'include');
    },

    // Listar especialidades de un departamento
    listar_especialidades(idDepartamento) {
        return Http.getOk(this.baseUrl + 'listar_especialidades.php?idDepartamento=' + idDepartamento, 'Error al cargar las especialidades', 'include');
    },

    // Listar profesores (con filtro de especialidad opcional)
    listar_profesores(idDepartamento, idEspecialidad, idEscenario) {
        let params = 'idDepartamento=' + idDepartamento;
        if (idEspecialidad) params += '&idEspecialidad=' + encodeURIComponent(idEspecialidad);
        return Http.getOk(this.baseUrl + 'listar_profesores.php?idEscenario=' + idEscenario + '&' + params, 'Error al cargar los profesores', 'include');
    },

    // Listar cursos con sus materias (fiel a v3)
    listar_cursos(idDepartamento, idEscenario) {
        return Http.getOk(this.baseUrl + 'listar_cursos.php?idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, 'Error al cargar los cursos', 'include');
    },

    // Listar la selección de un profesor
    listar_seleccion(idProfesor, idEscenario) {
        return Http.getOk(this.baseUrl + 'listar_seleccion.php?idProfesor=' + idProfesor + '&idEscenario=' + idEscenario, 'Error al cargar la selección', 'include');
    },

    // Nombres de los profesores que ya eligieron una materia
    listar_profesores_materia(idMateria, idGrupo, idEscenario) {
        return Http.getOk(this.baseUrl + 'listar_profesores_materia.php?idMateria=' + idMateria + '&idGrupo=' + idGrupo + '&idEscenario=' + idEscenario, 'Error al cargar los profesores de la materia', 'include');
    },

    // Insertar una selección
    async insertar_seleccion(data) {
        const res = await Http.post(this.baseUrl + 'insertar_seleccion.php?idEscenario=' + data.idEscenario, {
            idProfesor: data.idProfesor,
            idMateria: data.idMateria,
            idGrupo: data.idGrupo,
            horas: data.horas,
            idEscenario: data.idEscenario
        }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al guardar la selección');
        return res;
    },

    // Borrar una selección
    async borrar_seleccion(id) {
        const res = await Http.post(this.baseUrl + 'borrar_seleccion.php', { id: id }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al quitar la selección');
        return res;
    },

    // Vaciar la selección de un profesor
    async borrar_toda_seleccion(idProfesor, idEscenario) {
        const res = await Http.post(this.baseUrl + 'borrar_toda_seleccion.php', { idProfesor: idProfesor, idEscenario: idEscenario }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al vaciar la selección');
        return res;
    },

    // Vacía todas las selecciones del escenario (solo jefe de departamento o admin)
    async borrar_todas_selecciones(idEscenario) {
        const res = await Http.post(this.baseUrl + 'borrar_todas_selecciones.php?idEscenario=' + idEscenario, null, 'include');
        if (!res.success) throw new Error(res.error || 'Error al vaciar el escenario');
        return res;
    },

    // Reordena las selecciones del profesor; "ids" son los ids en el orden nuevo
    async ordenar_seleccion(ids, idEscenario) {
        const res = await Http.post(this.baseUrl + 'ordenar_seleccion.php?idEscenario=' + idEscenario, { ids: ids }, 'include');
        if (!res.success) throw new Error(res.error || 'Error al reordenar la selección');
        return res;
    }
};
