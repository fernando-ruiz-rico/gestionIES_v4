// API client para el módulo de Resultados de Aprendizaje (Fase 4.1)

const ResultadosAprendizajeAPI = {
    baseUrl: '../backend/api/resultados_aprendizaje.php',

    async listar_materias() {
        try {
            const response = await fetch(this.baseUrl + '?action=listar_materias', { credentials: 'include' });
            const data = await response.json();
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async cargar(idMateria) {
        try {
            const response = await fetch(this.baseUrl + '?action=cargar&idMateria=' + idMateria, { credentials: 'include' });
            const data = await response.json();
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async guardar(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=guardar', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async actualizar_horas(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=actualizar_horas', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async actualizar_evaluacion(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=actualizar_evaluacion', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async eliminar(id) {
        try {
            const response = await fetch(this.baseUrl + '?action=eliminar', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async obtener(id) {
        try {
            const response = await fetch(this.baseUrl + '?action=obtener&id=' + id, { credentials: 'include' });
            const data = await response.json();
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async guardar_criterio(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=guardar_criterio', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async actualizar_criterio(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=actualizar_criterio', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async eliminar_criterio(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=eliminar_criterio', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async cargar_criterios(idResultado) {
        try {
            const response = await fetch(this.baseUrl + '?action=cargar_criterios&idResultado=' + idResultado, { credentials: 'include' });
            const data = await response.json();
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    }
};
