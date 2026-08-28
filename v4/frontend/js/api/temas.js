// Cliente de la API de temas/unidades (Fase 2.6)
// Habla con ../backend/api/temas/ (un fichero por acción).
const TemasAPI = {
    baseUrl: '../backend/api/temas/',

    // --- Listados (GET) ---
    listarMaterias() {
        return Http.getOk(this.baseUrl + 'listar_materias.php', 'Error al cargar las materias');
    },

    listarTemas(idMateria) {
        return Http.getOk(`${this.baseUrl}listar.php?idMateria=${idMateria}`, 'Error al cargar los temas');
    },

    obtenerTema(idTema) {
        return Http.getOk(`${this.baseUrl}obtener.php?idTema=${idTema}`, 'Error al cargar el tema');
    },

    cargarAccordionRAyCE(idMateria) {
        return Http.getOk(`${this.baseUrl}accordion_ra_ce.php?idMateria=${idMateria}`, 'Error al cargar el acordeón RA/CE');
    },

    // --- Alta / modificación / borrado (POST) ---
    async nuevo(idMateria, orden, titulo) {
        const data = await Http.post(this.baseUrl + 'nuevo.php', { idMateria, orden, titulo });
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
        const data = await Http.post(this.baseUrl + 'recalcular_porcentajes.php', { idMateria });
        if (!data.success) throw new Error(data.error || 'Error al recalcular los porcentajes');
        return data;
    },

    async repetirEvaluacion(idMateria, evaluacion) {
        const data = await Http.post(this.baseUrl + 'repetir_evaluacion.php', { idMateria, evaluacion });
        if (!data.success) throw new Error(data.error || 'Error al copiar el campo de evaluación');
        return data;
    },

    // El porcentaje de evaluación ya no se edita a mano: se calcula en
    // recalcular_porcentajes. Aquí solo se actualiza el flag «RA/CE clave».
    async actualizarRA(idRA, es_clave) {
        const data = await Http.post(this.baseUrl + 'actualizar_ra.php', { idRA, es_clave });
        if (!data.success) throw new Error(data.error || 'Error al actualizar el resultado de aprendizaje');
        return data;
    }
};
