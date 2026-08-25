// API client para el módulo de Actas de departamentos (Fase 6.1)
//
// Convención unificada: los métodos de lectura resuelven con data.data y
// lanzan Error en caso de fallo; las acciones lanzan Error y resuelven con
// el sobre completo { success, data, message }.
const ActasAPI = {
    baseUrl: '../backend/api/actas/',

    // Lista las actas de un departamento (más reciente primero)
    listar(idDepartamento) {
        return Http.getOk(this.baseUrl + 'listar.php?idDepartamento=' + idDepartamento, 'Error al cargar las actas', 'include');
    },

    // Devuelve el texto y fecha de un acta
    obtener(idActa) {
        return Http.getOk(this.baseUrl + 'obtener.php?idActa=' + idActa, 'Error al cargar el acta', 'include');
    },

    // Devuelve el texto inicial de un acta nueva (profesores del
    // departamento en «Asistentes», fiel a v3 nueva_acta_departamento.php)
    nueva(idDepartamento) {
        return Http.getOk(this.baseUrl + 'nueva.php?idDepartamento=' + idDepartamento, 'Error al crear el acta', 'include');
    },

    // Inserta o actualiza un acta de departamento
    async guardar(data) {
        const parsed = await Http.post(this.baseUrl + 'guardar.php', data, 'include');
        if (!parsed.success) throw new Error(parsed.error || 'Error al guardar el acta');
        return parsed;
    }
};
