// FASE 2.1 — Cliente API de Programaciones Didácticas
//
// Habla con ../backend/api/programaciones/ (un fichero por acción).
//   - Listar/obtener/importar: modelo de programa (estado por materia).
//   - Edición de apartados (fiel a v3): cargar_materias, cargar_apartados,
//     cargar_contenido, guardar_contenido.
const API_URL = '../backend/api/programaciones/';

const ProgramacionesAPI = {
    listar(idMateria = null) {
        let url = `${API_URL}listar.php`;
        if (idMateria) {
            url += `?idMateria=${idMateria}`;
        }
        return Http.getOk(url, 'Error al listar programaciones');
    },

    obtener(idMateria) {
        return Http.getOk(`${API_URL}obtener.php?idMateria=${idMateria}`, 'Error al obtener programación');
    },

    // --- Edición de apartados (fiel a v3) ---
    // Materias con programación activa, ya filtradas por rol (v3/cargar_materias_programaciones.php).
    cargarMaterias() {
        return Http.getOk(`${API_URL}cargar_materias.php`, 'Error al cargar las materias');
    },

    // Apartados de una materia (v3/cargar_apartados.php).
    cargarApartados(idMateria) {
        return Http.getOk(`${API_URL}cargar_apartados.php?idMateria=${idMateria}`, 'Error al cargar los apartados');
    },

    // Texto de un apartado de una materia (v3/cargar_contenido_programacion.php).
    cargarContenido(idMateria, idApartado) {
        return Http.getOk(`${API_URL}cargar_contenido.php?idMateria=${idMateria}&idApartado=${idApartado}`, 'Error al cargar el contenido');
    },

    // Guardar el texto de un apartado editable (v3/insertar_contenido_programacion.php).
    async guardarContenido(idMateria, idApartado, texto) {
        const data = await Http.post(`${API_URL}guardar_contenido.php`, { idMateria, idApartado, texto });
        if (!data.success) throw new Error(data.error || 'Error al guardar el contenido');
        // Contrato unificado: sin_cambios viaja dentro de data.data
        return {
            success: true,
            sin_cambios: !!(data.data && data.data.sin_cambios),
            message: data.message
        };
    },

    // Marcar la propuesta pedagógica de una materia como terminada o no
    // (v4 propia: habilita importar la programación de aula).
    async actualizarTerminada(idMateria, terminada) {
        const data = await Http.post(`${API_URL}actualizar_terminada.php`, { idMateria, terminada });
        if (!data.success) throw new Error(data.error || 'Error al actualizar el estado de la propuesta');
        return data;
    },

    async importar(idMateriaOrigen, idMateriaDestino) {
        const data = await Http.post(`${API_URL}importar.php`, { idMateriaOrigen, idMateriaDestino });
        if (!data.success) throw new Error(data.error || 'Error al importar programación');
        return data;
    }
};
