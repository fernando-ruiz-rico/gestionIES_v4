// API para los contenidos por defecto de los temas / unidades (Fase 2.7)
// Backend de un solo archivo: backend/api/temas_contenidos_defecto.php
const TemasContenidosDefectoAPI = {
    baseUrl: '../backend/api/temas_contenidos_defecto.php',

    // Carga los contenidos por defecto de un departamento (contexto, recursos,
    // metodología y acciones)
    cargar(idDepartamento) {
        return Http.getOk(`${this.baseUrl}?action=cargar&idDepartamento=${idDepartamento}`, 'Error al cargar contenidos por defecto');
    },

    // Guarda (inserta o actualiza) los contenidos por defecto de un departamento
    async guardar(data) {
        const result = await Http.post(this.baseUrl, data);
        if (!result.success) throw new Error(result.error || 'Error al guardar contenidos por defecto');
        return result;
    }
};
