const programacionesApartadosAPI = {
    baseUrl: '../backend/api/programaciones_apartados/',

    async listar() {
        const response = await fetch(this.baseUrl + 'listar.php');
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al listar apartados');
        }
        return data.data;
    },

    async obtener(id) {
        const response = await fetch(`${this.baseUrl}obtener.php?id=${id}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al obtener apartado');
        }
        return data.data;
    },

    async guardar(apartado) {
        const response = await fetch(this.baseUrl + 'guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(apartado)
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar apartado');
        }
        return data;
    },

    async eliminar(id) {
        const response = await fetch(`${this.baseUrl}eliminar.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al eliminar apartado');
        }
        return data;
    },

    async ordenar(orden) {
        const formData = new FormData();
        formData.append('orden', orden);

        const response = await fetch(this.baseUrl + 'ordenar.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al ordenar apartados');
        }
        return data;
    }
};
