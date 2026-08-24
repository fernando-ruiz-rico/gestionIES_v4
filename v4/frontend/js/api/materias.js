// API client para el módulo de Materias

const MateriasAPI = {
    baseUrl: '../backend/api/materias/',

    // `idCurso` (opcional): filtra las materias del curso; sin él, todas (fiel a v3)
    async listar(idCurso) {
        try {
            const url = this.baseUrl + 'listar.php' + ((idCurso > 0) ? `?idCurso=${idCurso}` : '');
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: [] };
            }
            return { success: true, data: data.data };
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

    async guardar(materia) {
        try {
            const response = await fetch(this.baseUrl + 'guardar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(materia)
            });
            return await response.json();
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
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Datos de una materia por grupo (fiel a v3: cargar_forms_materias_grupos.php)
    async listar_materias_grupos(idMateria, idCurso) {
        try {
            const url = this.baseUrl + `listar_materias_grupos.php?idMateria=${idMateria}&idCurso=${idCurso}`;
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            console.error(e);
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    // Inserta/modifica los datos de una materia para un grupo (fiel a v3)
    async insertar_materia_grupo(datos) {
        try {
            const response = await fetch(this.baseUrl + 'insertar_materia_grupo.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Lista de competencias asociadas a una materia + opciones del ciclo
    async competencias_listar(idMateria) {
        try {
            const url = this.baseUrl + `competencias_listar.php?idMateria=${idMateria}`;
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (e) {
            console.error(e);
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    // Asocia una competencia a una materia (solo admin, fiel a v3)
    async competencias_asociar(idMateria, idCompetencia) {
        try {
            const response = await fetch(this.baseUrl + 'competencias_asociar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idMateria: idMateria, idCompetencia: idCompetencia })
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    },

    // Desasocia una competencia de una materia (solo admin, fiel a v3)
    async competencias_borrar(idMateria, idCompetencia) {
        try {
            const response = await fetch(this.baseUrl + 'competencias_borrar.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idMateria: idMateria, idCompetencia: idCompetencia })
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: 'Error de conexión' };
        }
    }
};
