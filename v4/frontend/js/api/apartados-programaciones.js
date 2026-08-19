const apartadosProgramacionesAPI = {
    listar(idProgramacion) {
        let url = '../backend/api/apartados_programaciones/listar.php';
        if (idProgramacion) {
            url += '?id_programacion=' + idProgramacion;
        }
        return fetch(url)
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
    },
    
    obtener(id) {
        return fetch('../backend/api/apartados_programaciones/obtener.php?id=' + id)
            .then(response => response.json());
    },
    
    guardar(datos) {
        return fetch('../backend/api/apartados_programaciones/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        }).then(response => response.json());
    },
    
    eliminar(id) {
        return fetch('../backend/api/apartados_programaciones/eliminar.php?id=' + id)
            .then(response => response.json());
    }
};
