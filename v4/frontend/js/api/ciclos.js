// API client para el módulo de Ciclos

const CiclosAPI = {
    baseUrl: '../backend/api/ciclos/',

    async listar() {
        try {
            const response = await fetch(this.baseUrl + 'listar.php', { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: [] };
            }
            return { success: true, data: Array.isArray(data.data) ? data.data : [] };
        } catch (e) {
            console.error(e);
            return { success: false, error: 'Error de conexión', data: [] };
        }
    },

    async obtener(id) {
        try {
            const response = await fetch(this.baseUrl + `obtener.php?id=${id}`, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    async guardar(ciclo) {
        try {
            const response = await fetch(this.baseUrl + 'guardar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(ciclo)
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async eliminar(id) {
        try {
            const response = await fetch(this.baseUrl + 'eliminar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Asociaciones de cursos con el ciclo

    async asociacionesCursos(idCiclo) {
        try {
            const response = await fetch(this.baseUrl + `asociaciones_cursos.php?idCiclo=${idCiclo}`, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    async guardarAsociacionCurso(asociacion) {
        try {
            const response = await fetch(this.baseUrl + 'guardar_asociacion_curso.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(asociacion)
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async borrarAsociacionCurso(idCiclo, idCurso) {
        try {
            const response = await fetch(this.baseUrl + 'borrar_asociacion_curso.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCiclo: idCiclo, idCurso: idCurso })
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Asociaciones de unidades de competencia con el ciclo

    async asociacionesUnidades(idCiclo) {
        try {
            const response = await fetch(this.baseUrl + `asociaciones_unidades.php?idCiclo=${idCiclo}`, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    async guardarAsociacionUnidad(idCiclo, codigoUnidad) {
        try {
            const response = await fetch(this.baseUrl + 'guardar_asociacion_unidad.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCiclo: idCiclo, codigoUnidad: codigoUnidad })
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    async borrarAsociacionUnidad(idCiclo, codigoUnidad) {
        try {
            const response = await fetch(this.baseUrl + 'borrar_asociacion_unidad.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCiclo: idCiclo, codigoUnidad: codigoUnidad })
            });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión' };
            }
            return data;
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    }
};
