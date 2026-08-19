const contenidosDefectoTemasAPI = {
    listar() {
        return fetch('../backend/api/contenidos_defecto_temas/listar.php')
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
    },
    
    obtener(id) {
        return fetch('../backend/api/contenidos_defecto_temas/obtener.php?id=' + id)
            .then(response => response.json());
    },
    
    guardar(datos) {
        return fetch('../backend/api/contenidos_defecto_temas/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        }).then(response => response.json());
    },
    
    eliminar(id) {
        return fetch('../backend/api/contenidos_defecto_temas/eliminar.php?id=' + id)
            .then(response => response.json());
    }
};
