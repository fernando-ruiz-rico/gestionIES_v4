// Cliente de la API de temas/unidades (Fase 2.6)
// Un único endpoint (temas.php) con acciones GET/POST, como en v3.
const temasAPI = {
    baseUrl: '../backend/api/',

    // --- Listados (GET) ---
    async listarMaterias() {
        const response = await fetch(this.baseUrl + 'temas.php?action=listar_materias');
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar las materias');
        }
        return data.data;
    },

    async listarTemas(idMateria) {
        const response = await fetch(`${this.baseUrl}temas.php?action=listar&idMateria=${idMateria}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los temas');
        }
        return data.data;
    },

    async obtenerTema(idTema) {
        const response = await fetch(`${this.baseUrl}temas.php?action=obtener&idTema=${idTema}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el tema');
        }
        return data.data;
    },

    async cargarAccordionRAyCE(idMateria) {
        const response = await fetch(`${this.baseUrl}temas.php?action=accordion_ra_ce&idMateria=${idMateria}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el acordeón RA/CE');
        }
        return data.data;
    },

    // --- Alta / modificación / borrado (POST) ---
    async nuevo(idMateria, orden, titulo) {
        const response = await fetch(this.baseUrl + 'temas.php?action=nuevo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idMateria, orden, titulo })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al crear el tema');
        }
        return data;
    },

    async guardar(payload) {
        const response = await fetch(this.baseUrl + 'temas.php?action=guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar el tema');
        }
        return data;
    },

    async borrar(idTema) {
        const response = await fetch(this.baseUrl + 'temas.php?action=borrar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idTema })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al borrar el tema');
        }
        return data;
    },

    async recalcularPorcentajes(idMateria) {
        const response = await fetch(this.baseUrl + 'temas.php?action=recalcular_porcentajes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idMateria })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al recalcular los porcentajes');
        }
        return data;
    },

    async repetirEvaluacion(idMateria, evaluacion) {
        const response = await fetch(this.baseUrl + 'temas.php?action=repetir_evaluacion', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idMateria, evaluacion })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al copiar el campo de evaluación');
        }
        return data;
    },

    async actualizarRA(idRA, porcentaje_evaluacion, es_clave) {
        const response = await fetch(this.baseUrl + 'temas.php?action=actualizar_ra', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idRA, porcentaje_evaluacion, es_clave })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al actualizar el resultado de aprendizaje');
        }
        return data;
    }
};
