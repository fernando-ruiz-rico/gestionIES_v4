// API del frontend para la gestión de los apartados del PCCF (Fase 3.2)
const PCCFApartadosAPI = {
    baseUrl: '../backend/api/pccf_apartados/',

    // Lista los apartados del PCCF
    listar() {
        return Http.getOk(`${this.baseUrl}listar.php`, 'Error al listar los apartados');
    },

    // Devuelve los datos de un apartado concreto
    obtener(id) {
        return Http.getOk(`${this.baseUrl}obtener.php?id=${id}`, 'Error al obtener el apartado');
    },

    // Inserta o actualiza un apartado
    async guardar(apartado) {
        const data = await Http.post(this.baseUrl + 'guardar.php', apartado);
        if (!data.success) throw new Error(data.error || 'Error al guardar el apartado');
        return data;
    },

    // Elimina un apartado (y sus contenidos)
    async eliminar(id) {
        const data = await Http.del(`${this.baseUrl}borrar.php?id=${id}`);
        if (!data.success) throw new Error(data.error || 'Error al eliminar el apartado');
        return data;
    },

    // Reordena los apartados según el nuevo orden recibido
    async ordenar(orden) {
        const formData = new FormData();
        formData.append('orden', orden);
        const data = await Http.post(this.baseUrl + 'ordenar.php', formData);
        if (!data.success) throw new Error(data.error || 'Error al ordenar los apartados');
        return data;
    }
};
