const API_URL = '../backend/api/programaciones/index.php';

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

    async obtener(idMateria) {
        try {
            const response = await fetch(`${API_URL}?action=obtener&idMateria=${idMateria}`);
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

    // FASE 2.1 — guardar/eliminar eliminados: en el modelo fiel a v3 no existe una
    // fila única de programación (se editan sus apartados/contenidos en las fases 2.2-2.5).
    async importar(idMateriaOrigen, idMateriaDestino) {
        try {
            const response = await fetch(`${API_URL}?action=importar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idMateriaOrigen: idMateriaOrigen,
                    idMateriaDestino: idMateriaDestino
                })
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al importar programación');
            }
            return data;
        } catch (error) {
            console.error('Error en importar programación:', error);
            throw error;
        }
    }
};
