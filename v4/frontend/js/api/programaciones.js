// FASE 2.1 — Cliente API de Programaciones Didácticas
//
// Habla con ../backend/api/programaciones/index.php.
//   - Listar/obtener/importar: modelo de programa (estado por materia).
//   - Edición de apartados (fiel a v3): cargar_materias, cargar_apartados,
//     cargar_contenido, guardar_contenido.
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

    // --- Edición de apartados (fiel a v3) ---
    // Materias con programación activa, ya filtradas por rol (v3/cargar_materias_programaciones.php).
    async cargarMaterias() {
        try {
            const response = await fetch(`${API_URL}?action=cargar_materias`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar las materias');
            }
            return data.data;
        } catch (error) {
            console.error('Error en cargar materias:', error);
            throw error;
        }
    },

    // Apartados de una materia (v3/cargar_apartados.php).
    async cargarApartados(idMateria) {
        try {
            const response = await fetch(`${API_URL}?action=cargar_apartados&idMateria=${idMateria}`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar los apartados');
            }
            return data.data;
        } catch (error) {
            console.error('Error en cargar apartados:', error);
            throw error;
        }
    },

    // Texto de un apartado de una materia (v3/cargar_contenido_programacion.php).
    async cargarContenido(idMateria, idApartado) {
        try {
            const response = await fetch(`${API_URL}?action=cargar_contenido&idMateria=${idMateria}&idApartado=${idApartado}`);
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar el contenido');
            }
            return data.data;
        } catch (error) {
            console.error('Error en cargar contenido:', error);
            throw error;
        }
    },

    // Guardar el texto de un apartado editable (v3/insertar_contenido_programacion.php).
    async guardarContenido(idMateria, idApartado, texto) {
        try {
            const response = await fetch(`${API_URL}?action=guardar_contenido`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    idMateria: idMateria,
                    idApartado: idApartado,
                    texto: texto
                })
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Error al guardar el contenido');
            }
            return data;
        } catch (error) {
            console.error('Error en guardar contenido:', error);
            throw error;
        }
    },

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
