// API client para el módulo de Actas de departamentos (Fase 6.1)

const ActasAPI = {
    baseUrl: '../backend/api/actas.php',

    // Lista las actas de un departamento (más reciente primero)
    async listar(idDepartamento) {
        try {
            const response = await fetch(this.baseUrl + '?action=listar&idDepartamento=' + idDepartamento, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: [] };
            }
            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error al listar las actas:', error);
            return { success: false, error: 'Error de conexión', data: [] };
        }
    },

    // Devuelve el texto y fecha de un acta
    async obtener(idActa) {
        try {
            const response = await fetch(this.baseUrl + '?action=obtener&idActa=' + idActa, { credentials: 'include' });
            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error de conexión', data: null };
            }
            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error al obtener el acta:', error);
            return { success: false, error: 'Error de conexión', data: null };
        }
    },

    // Inserta o actualiza un acta de departamento
    async guardar(data) {
        try {
            const response = await fetch(this.baseUrl + '?action=guardar', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const parsed = await response.json();
            if (!parsed.success) {
                return { success: false, error: parsed.error || 'Error de conexión' };
            }
            return { success: true, data: parsed.data };
        } catch (error) {
            console.error('Error al guardar el acta:', error);
            return { success: false, error: 'Error de conexión' };
        }
    }
};
