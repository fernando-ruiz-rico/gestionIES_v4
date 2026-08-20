const API_URL = 'backend/api/programaciones_contenidos/index.php';

const programacionesContenidosAPI = {
    async cargarApartados(idMateria) {
        try {
            const response = await fetch(`${API_URL}?action=cargar_apartados&idMateria=${idMateria}`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar apartados');
            }
            return data.data;
        } catch (error) {
            console.error('Error en cargar apartados:', error);
            throw error;
        }
    },

    async cargarContenido(idMateria, idApartado) {
        try {
            const response = await fetch(`${API_URL}?action=cargar_contenido&idMateria=${idMateria}&idApartado=${idApartado}`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar contenido');
            }
            return data.data.texto || '';
        } catch (error) {
            console.error('Error en cargar contenido:', error);
            throw error;
        }
    },

    async guardarContenido(idMateria, idApartado, texto) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idMateria: idMateria,
                    idApartado: idApartado,
                    texto: texto
                })
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al guardar contenido');
            }
            return data;
        } catch (error) {
            console.error('Error en guardar contenido:', error);
            throw error;
        }
    }
};
