// API client para el módulo de Resultados de Aprendizaje (Fase 4.1)

const ResultadosArendizajeAPI = {
    baseUrl: '../backend/api/resultados_aprendizaje.php',

    // Lista las materias del selector. El departamento del admin se pasa
    // como parámetro; el jefe y el profesor usan el de su sesión (como en v3).
    async listar_materias(idDepartamento) {
        try {
            const url = this.baseUrl + '?action=listar_materias' +
                (idDepartamento ? '&idDepartamento=' + idDepartamento : '');
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) return { success: false, error: data.error || 'Error de conexión' };
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async cargar(idMateria) {
        try {
            const response = await fetch(this.baseUrl + '?action=cargar&idMateria=' + idMateria, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) return { success: false, error: data.error || 'Error de conexión' };
            return { success: true, data: data.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async obtener(id) {
        try {
            const response = await fetch(this.baseUrl + '?action=obtener&id=' + id, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) return { success: false, error: data.error || 'Error de conexión' };
            return { success: true, data: data.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
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
            const res = await response.json();
            if (!res.success) return { success: false, error: res.error || 'Error de conexión' };
            return { success: true, data: res.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async cargar_criterios(idResultado) {
        try {
            const response = await fetch(this.baseUrl + '?action=cargar_criterios&idResultado=' + idResultado, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) return { success: false, error: data.error || 'Error de conexión' };
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    }
};
