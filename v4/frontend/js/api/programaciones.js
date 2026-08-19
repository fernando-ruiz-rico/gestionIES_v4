const API_URL = 'backend/api/programaciones/index.php';

const programacionesAPI = {
    async listar(idMateria = null) {
        try {
            let url = `${API_URL}?action=listar`;
            if (idMateria) {
                url += `&idMateria=${idMateria}`;
            }
            const response = await fetch(url);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al listar programaciones');
            }
            return data.data;
        } catch (error) {
            console.error('Error en listar programaciones:', error);
            throw error;
        }
    },

    async obtener(id) {
        try {
            const response = await fetch(`${API_URL}?action=obtener&id=${id}`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al obtener programación');
            }
            return data.data;
        } catch (error) {
            console.error('Error en obtener programación:', error);
            throw error;
        }
    },

    async guardar(programacion) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(programacion)
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al guardar programación');
            }
            return data;
        } catch (error) {
            console.error('Error en guardar programación:', error);
            throw error;
        }
    },

    async eliminar(id) {
        try {
            const response = await fetch(`${API_URL}?action=eliminar&id=${id}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al eliminar programación');
            }
            return data;
        } catch (error) {
            console.error('Error en eliminar programación:', error);
            throw error;
        }
    }
};
