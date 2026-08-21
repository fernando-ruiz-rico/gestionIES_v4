// API client para el módulo de Selección de materias (Fase 5.1)

const SeleccionAPI = {
    baseUrl: '../backend/api/seleccion.php',

    async listar_cursos(idDepartamento, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar_cursos&idDepartamento=' + idDepartamento + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    },

    async listar_profesores(idDepartamento, idEspecialidad) {
        const params = 'idDepartamento=' + idDepartamento;
        if (idEspecialidad) params += '&idEspecialidad=' + encodeURIComponent(idEspecialidad);
        const res = await fetch(this.baseUrl + '?action=listar_profesores&' + params, { credentials: 'include' });
        return res.json();
    },

    async listar_seleccion(idProfesor, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=listar_seleccion&idProfesor=' + idProfesor + '&idEscenario=' + idEscenario, { credentials: 'include' });
        return res.json();
    },

    async insertar_seleccion(data) {
        const res = await fetch(this.baseUrl + '?action=insertar_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },

    async borrar_seleccion(id) {
        const res = await fetch(this.baseUrl + '?action=borrar_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        return res.json();
    },

    async borrar_toda_seleccion(idProfesor, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=borrar_toda_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idProfesor: idProfesor, idEscenario: idEscenario })
        });
        return res.json();
    },

    async ordenar_seleccion(orden, idEscenario) {
        const res = await fetch(this.baseUrl + '?action=ordenar_seleccion', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orden: orden, idEscenario: idEscenario })
        });
        return res.json();
    }
};
