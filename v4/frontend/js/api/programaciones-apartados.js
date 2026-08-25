const ProgramacionesApartadosAPI = {
    baseUrl: '../backend/api/programaciones_apartados/',

    listar() {
        return Http.getOk(this.baseUrl + 'listar.php', 'Error al listar apartados');
    },

    obtener(id) {
        return Http.getOk(`${this.baseUrl}obtener.php?id=${id}`, 'Error al obtener apartado');
    },

    async guardar(apartado) {
        const data = await Http.post(this.baseUrl + 'guardar.php', apartado);
        if (!data.success) throw new Error(data.error || 'Error al guardar apartado');
        return data;
    },

    async eliminar(id) {
        const data = await Http.del(`${this.baseUrl}eliminar.php?id=${id}`);
        if (!data.success) throw new Error(data.error || 'Error al eliminar apartado');
        return data;
    },

    async ordenar(orden) {
        const data = await Http.post(this.baseUrl + 'ordenar.php', { orden: orden });
        if (!data.success) throw new Error(data.error || 'Error al ordenar apartados');
        return data;
    }
};
