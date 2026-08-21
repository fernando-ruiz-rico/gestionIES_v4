// API para los contenidos por defecto de los temas / unidades (Fase 2.7)
// Backend de un solo archivo: backend/api/temas_contenidos_defecto.php
const TemasContenidosDefectoAPI = {
    baseUrl: '../backend/api/temas_contenidos_defecto.php',

    // Carga los contenidos por defecto de un departamento (contexto, recursos,
    // metodología y acciones)
    async cargar(idDepartamento) {
        const response = await fetch(`${this.baseUrl}?action=cargar&idDepartamento=${idDepartamento}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar contenidos por defecto');
        }
        return data.data;
    },

    // Guarda (inserta o actualiza) los contenidos por defecto de un departamento
    async guardar(data) {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Error al guardar contenidos por defecto');
        }
        return result;
    }
};
