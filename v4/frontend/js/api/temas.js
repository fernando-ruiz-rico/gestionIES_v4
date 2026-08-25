// Cliente de la API de temas/unidades (Fase 2.6)
// Un único endpoint (temas.php) con acciones GET/POST, como en v3.
const temasAPI = {
    baseUrl: '../backend/api/',

    // --- Listados (GET) ---
    listarMaterias() {
        return Http.getOk(this.baseUrl + 'temas.php?action=listar_materias', 'Error al cargar las materias');
    },

    listarTemas(idMateria) {
        return Http.getOk(`${this.baseUrl}temas.php?action=listar&idMateria=${idMateria}`, 'Error al cargar los temas');
    },

    obtenerTema(idTema) {
        return Http.getOk(`${this.baseUrl}temas.php?action=obtener&idTema=${idTema}`, 'Error al cargar el tema');
    },

    cargarAccordionRAyCE(idMateria) {
        return Http.getOk(`${this.baseUrl}temas.php?action=accordion_ra_ce&idMateria=${idMateria}`, 'Error al cargar el acordeón RA/CE');
    },

    // --- Alta / modificación / borrado (POST) ---
    async nuevo(idMateria, orden, titulo) {
        const data = await Http.post(this.baseUrl + 'temas.php?action=nuevo', { idMateria, orden, titulo });
        if (!data.success) throw new Error(data.error || 'Error al crear el tema');
        return data;
    },

    guardar(payload) {
        return Http.postOk(this.baseUrl + 'temas.php?action=guardar', payload, 'Error al guardar el tema');
    },

    async borrar(idTema) {
        const data = await Http.post(this.baseUrl + 'temas.php?action=borrar', { id: idTema });
        if (!data.success) throw new Error(data.error || 'Error al borrar el tema');
        return data;
    },

    async recalcularPorcentajes(idMateria) {
        const data = await Http.post(this.baseUrl + 'temas.php?action=recalcular_porcentajes', { idMateria });
        if (!data.success) throw new Error(data.error || 'Error al recalcular los porcentajes');
        return data;
    },

    async repetirEvaluacion(idMateria, evaluacion) {
        const data = await Http.post(this.baseUrl + 'temas.php?action=repetir_evaluacion', { idMateria, evaluacion });
        if (!data.success) throw new Error(data.error || 'Error al copiar el campo de evaluación');
        return data;
    },

    async actualizarRA(idRA, porcentaje_evaluacion, es_clave) {
        const data = await Http.post(this.baseUrl + 'temas.php?action=actualizar_ra', { idRA, porcentaje_evaluacion, es_clave });
        if (!data.success) throw new Error(data.error || 'Error al actualizar el resultado de aprendizaje');
        return data;
    }
};
