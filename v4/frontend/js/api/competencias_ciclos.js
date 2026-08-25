// API client para el módulo de Competencias por Ciclo (Fase 4.2)

const CompetenciasCiclosAPI = {
    baseUrl: '../backend/api/competencias_ciclos.php',

    listar_ciclos() {
        return Http.get(this.baseUrl + '?action=listar_ciclos', 'include');
    },

    listar(idCiclo) {
        return Http.get(this.baseUrl + '?action=listar&idCiclo=' + idCiclo, 'include');
    },

    obtener(id) {
        return Http.get(this.baseUrl + '?action=obtener&id=' + id, 'include');
    },

    guardar(data) {
        return Http.post(this.baseUrl + '?action=guardar', data, 'include');
    },

    ordenar(orden) {
        return Http.post(this.baseUrl + '?action=ordenar', { orden: orden }, 'include');
    },

    eliminar(id) {
        return Http.post(this.baseUrl + '?action=eliminar', { id: id }, 'include');
    }
};
