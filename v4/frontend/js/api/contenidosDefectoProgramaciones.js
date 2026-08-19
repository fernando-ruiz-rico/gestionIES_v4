const contenidosDefectoProgramacionesAPI = {
    listar() {
        return fetch('../backend/api/contenidos_defecto_programaciones/listar.php')
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
    },
    
    obtener(id) {
        return fetch('../backend/api/contenidos_defecto_programaciones/obtener.php?id=' + id)
            .then(response => response.json());
    },
    
    guardar(datos) {
        return fetch('../backend/api/contenidos_defecto_programaciones/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        }).then(response => response.json());
    },
    
    eliminar(id) {
        return fetch('../backend/api/contenidos_defecto_programaciones/eliminar.php?id=' + id)
            .then(response => response.json());
    }
};
