const programacionesContenidosDefectoAPI = {
    baseUrl: '../backend/api/programaciones_contenidos_defecto/',

    async cargar(idApartado, idDepartamento) {
        const response = await fetch(`${this.baseUrl}cargar.php?idApartado=${idApartado}&idDepartamento=${idDepartamento}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar contenido por defecto');
        }
        return data.data;
    },

    async guardar(idApartado, idDepartamento, texto) {
        const response = await fetch(this.baseUrl + 'guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idApartado,
                idDepartamento,
                texto
            })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar contenido por defecto');
        }
        return data;
    }
};
