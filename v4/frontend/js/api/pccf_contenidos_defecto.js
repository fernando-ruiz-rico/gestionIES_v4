// API del frontend para los contenidos por defecto del PCCF (Fase 3.3)
const PCCFContenidosDefectoAPI = {
    baseUrl: '../backend/api/pccf_contenidos_defecto/',

    async cargar(idApartado, idDepartamento) {
        const response = await fetch(`${this.baseUrl}cargar.php?idApartado=${idApartado}&idDepartamento=${idDepartamento}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el contenido por defecto');
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
            throw new Error(data.error || 'Error al guardar el contenido por defecto');
        }
        return data;
    }
};
