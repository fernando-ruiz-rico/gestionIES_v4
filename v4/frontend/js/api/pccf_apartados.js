// API del frontend para la gestión de los apartados del PCCF (Fase 3.2)
const PCCFApartadosAPI = {
    baseUrl: '../backend/api/pccf_apartados/',

    // Lista los apartados del PCCF
    async listar() {
        const response = await fetch(`${this.baseUrl}listar.php`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al listar los apartados');
        }
        return data.data;
    },

    // Devuelve los datos de un apartado concreto
    async obtener(id) {
        const response = await fetch(`${this.baseUrl}obtener.php?id=${id}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al obtener el apartado');
        }
        return data.data;
    },

    // Inserta o actualiza un apartado
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
            throw new Error(data.error || 'Error al guardar el apartado');
        }
        return data;
    },

    // Elimina un apartado (y sus contenidos)
    async eliminar(id) {
        const response = await fetch(`${this.baseUrl}borrar.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al eliminar el apartado');
        }
        return data;
    },

    // Reordena los apartados según el nuevo orden recibido
    async ordenar(orden) {
        const formData = new FormData();
        formData.append('orden', orden);
        const response = await fetch(this.baseUrl + 'ordenar.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al ordenar los apartados');
        }
        return data;
    }
};
