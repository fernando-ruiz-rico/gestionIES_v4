// Cliente de la API de temas/unidades de la copia de aula (Programaciones de
// aula). Habla con ../backend/api/temas_aula/ (un fichero por acción), espejo
// de TemasAPI sobre las tablas de aula.
//
// A diferencia de TemasAPI, necesita el (idGrupo, idProfesor) de la copia en
// las acciones que operan por materia (listar, acordeón, recalcular,
// repetir_evaluacion, nuevo...). Se fijan con setContext(idGrupo, idProfesor)
// al montar la vista; las acciones por id (obtener/guardar/borrar/RA) no las
// necesitan, pues los ids de la copia son únicos.
const TemasAulaAPI = {
    baseUrl: '../backend/api/temas_aula/',
    idGrupo: 0,
    idProfesor: 0,

    // Fija el (grupo, profesor) de la copia para las siguientes peticiones.
    setContext(idGrupo, idProfesor) {
        this.idGrupo = idGrupo || 0;
        this.idProfesor = idProfesor || 0;
    },

    _q() {
        return 'idGrupo=' + this.idGrupo + '&idProfesor=' + this.idProfesor;
    },

    // --- Listados (GET) ---
    listarMaterias() {
        return Http.getOk(this.baseUrl + 'listar_materias.php?' + this._q(), 'Error al cargar las materias');
    },

    listarTemas(idMateria) {
        return Http.getOk(`${this.baseUrl}listar.php?idMateria=${idMateria}&${this._q()}`, 'Error al cargar los temas');
    },

    obtenerTema(idTema) {
        return Http.getOk(`${this.baseUrl}obtener.php?idTema=${idTema}`, 'Error al cargar el tema');
    },

    cargarAccordionRAyCE(idMateria) {
        return Http.getOk(`${this.baseUrl}accordion_ra_ce.php?idMateria=${idMateria}&${this._q()}`, 'Error al cargar el acordeón RA/CE');
    },

    // --- Alta / modificación / borrado (POST) ---
    async nuevo(idMateria, orden, titulo) {
        const data = await Http.post(this.baseUrl + 'nuevo.php', {
            idMateria, orden, titulo, idGrupo: this.idGrupo, idProfesor: this.idProfesor
        });
        if (!data.success) throw new Error(data.error || 'Error al crear el tema');
        return data;
    },

    guardar(payload) {
        return Http.postOk(this.baseUrl + 'guardar.php', payload, 'Error al guardar el tema');
    },

    async borrar(idTema) {
        const data = await Http.post(this.baseUrl + 'borrar.php', { id: idTema });
        if (!data.success) throw new Error(data.error || 'Error al borrar el tema');
        return data;
    },

    async recalcularPorcentajes(idMateria) {
        const data = await Http.post(this.baseUrl + 'recalcular_porcentajes.php', {
            idMateria, idGrupo: this.idGrupo, idProfesor: this.idProfesor
        });
        if (!data.success) throw new Error(data.error || 'Error al recalcular los porcentajes');
        return data;
    },

    async repetirEvaluacion(idMateria, evaluacion) {
        const data = await Http.post(this.baseUrl + 'repetir_evaluacion.php', {
            idMateria, evaluacion, idGrupo: this.idGrupo, idProfesor: this.idProfesor
        });
        if (!data.success) throw new Error(data.error || 'Error al copiar el campo de evaluación');
        return data;
    },

    async actualizarRA(idRA, porcentaje_evaluacion, es_clave) {
        const data = await Http.post(this.baseUrl + 'actualizar_ra.php', { idRA, porcentaje_evaluacion, es_clave });
        if (!data.success) throw new Error(data.error || 'Error al actualizar el resultado de aprendizaje');
        return data;
    }
};
