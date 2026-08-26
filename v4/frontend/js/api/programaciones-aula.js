// FASE 2.4 — Cliente API de Programaciones de aula (opción propia de v4).
//
// Habla con ../backend/api/programaciones_aula/.
// La programación de aula es una copia, por profesor y grupo, de la
// propuesta pedagógica de la materia (terminada), y se edita por apartados
// igual que la propuesta pedagógica. Un profesor siempre opera sobre sí
// mismo (idProfesor = 0 → el backend usa la sesión); un jefe/admin elige
// profesor (idProfesor > 0).
const ProgramacionesAulaAPI = {
    baseUrl: '../backend/api/programaciones_aula/',

    // Todos los profesores (solo jefe/admin)
    cargarProfesores() {
        return Http.getOk(this.baseUrl + 'profesores.php', 'Error al cargar los profesores');
    },

    // Materias con programación del profesor (todos sus grupos, escenario
    // actual, con flag "terminada"): como en la propuesta pedagógica, primero
    // se elige la materia y luego el grupo.
    cargarMaterias(idProfesor) {
        let url = this.baseUrl + 'materias.php';
        if (idProfesor > 0) {
            url += `?idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar las materias');
    },

    // Grupos que imparte el profesor en la materia elegida (escenario actual)
    cargarGrupos(idMateria, idProfesor) {
        let url = `${this.baseUrl}grupos.php?idMateria=${idMateria}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar los grupos');
    },

    // Apartados de la materia (mismo catálogo que la propuesta pedagógica)
    cargarApartados(idMateria) {
        return Http.getOk(`${this.baseUrl}apartados.php?idMateria=${idMateria}`, 'Error al cargar los apartados');
    },

    // Texto de un apartado de la programación de aula
    cargarContenido(idMateria, idApartado, idGrupo, idProfesor) {
        let url = `${this.baseUrl}cargar_contenido.php?idMateria=${idMateria}&idApartado=${idApartado}&idGrupo=${idGrupo}`;
        if (idProfesor > 0) {
            url += `&idProfesor=${idProfesor}`;
        }
        return Http.getOk(url, 'Error al cargar el contenido');
    },

    // Guardar el texto de un apartado (contrato sin_cambios como la propuesta)
    async guardar(idMateria, idApartado, idGrupo, idProfesor, texto) {
        const data = await Http.post(this.baseUrl + 'guardar.php', { idMateria, idApartado, idGrupo, idProfesor, texto });
        if (!data.success) throw new Error(data.error || 'Error al guardar el contenido');
        return {
            success: true,
            sin_cambios: !!(data.data && data.data.sin_cambios),
            message: data.message
        };
    },

    // Importar: copia, para el profesor y el grupo elegidos, de la propuesta
    // pedagógica de la materia (que debe estar marcada como terminada)
    async importar(idMateria, idGrupo, idProfesor) {
        const data = await Http.post(this.baseUrl + 'importar.php', { idMateria, idGrupo, idProfesor });
        if (!data.success) throw new Error(data.error || 'Error al importar la programación de aula');
        return data;
    }
};
