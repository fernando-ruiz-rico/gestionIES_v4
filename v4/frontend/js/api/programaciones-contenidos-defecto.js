const programacionesContenidosDefectoAPI = {
    baseUrl: '../backend/api/programaciones_contenidos_defecto/',

    cargar(idApartado, idDepartamento) {
        return Http.getOk(`${this.baseUrl}cargar.php?idApartado=${idApartado}&idDepartamento=${idDepartamento}`, 'Error al cargar contenido por defecto');
    },

    // Guarda (inserta o actualiza) el contenido por defecto de un apartado
    async guardar(idApartado, idDepartamento, texto) {
        const data = await Http.post(this.baseUrl + 'guardar.php', { idApartado, idDepartamento, texto });
        if (!data.success) throw new Error(data.error || 'Error al guardar contenido por defecto');
        return data;
    }
};
