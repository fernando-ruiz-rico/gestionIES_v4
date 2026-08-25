// API del frontend para el PCCF (Fase 3.1)
const PCCFAPI = {
    baseUrl: '../backend/api/pccf/',

    // Lista los ciclos formativos disponibles
    listarCiclos() {
        return Http.getOk(`${this.baseUrl}listar_ciclos.php`, 'Error al listar los ciclos');
    },

    // Devuelve el contenido del ciclo (todos los apartados) o de un apartado concreto
    listar(idCiclo, idApartado = null) {
        let url = `${this.baseUrl}listar.php?idCiclo=${idCiclo}`;
        if (idApartado) {
            url += `&idApartado=${idApartado}`;
        }
        return Http.getOk(url, 'Error al cargar el contenido');
    },

    // Guarda (inserta/actualiza) o elimina el contenido de un ciclo y apartado
    async guardar(idCiclo, idApartado, texto) {
        const data = await Http.post(this.baseUrl + 'guardar.php', { idCiclo, idApartado, texto });
        if (!data.success) throw new Error(data.error || 'Error al guardar el contenido');
        return data;
    }
};
