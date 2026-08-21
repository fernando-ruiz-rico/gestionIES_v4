// API del frontend para el PCCF (Fase 3.1)
const PCCFAPI = {
    baseUrl: '../backend/api/pccf/',

    // Lista los ciclos formativos disponibles
    async listarCiclos() {
        const response = await fetch(`${this.baseUrl}listar_ciclos.php`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al listar los ciclos');
        }
        return data.data;
    },

    // Devuelve el contenido del ciclo (todos los apartados) o de un apartado concreto
    async listar(idCiclo, idApartado = null) {
        let url = `${this.baseUrl}listar.php?idCiclo=${idCiclo}`;
        if (idApartado) {
            url += `&idApartado=${idApartado}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar el contenido');
        }
        return data.data;
    },

    // Guarda (inserta/actualiza) o elimina el contenido de un ciclo y apartado
    async guardar(idCiclo, idApartado, texto) {
        const response = await fetch(this.baseUrl + 'guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idCiclo,
                idApartado,
                texto
            })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Error al guardar el contenido');
        }
        return data;
    }
};
