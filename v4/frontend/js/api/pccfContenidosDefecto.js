const pccfContenidosDefectoAPI = {
    listar() {
        return fetch('../backend/api/pccf_contenidos_defecto/listar.php')
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
    },
    
    obtener(id) {
        return fetch('../backend/api/pccf_contenidos_defecto/obtener.php?id=' + id)
            .then(response => response.json());
    },
    
    guardar(datos) {
        return fetch('../backend/api/pccf_contenidos_defecto/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        }).then(response => response.json());
    },
    
    eliminar(id) {
        return fetch('../backend/api/pccf_contenidos_defecto/eliminar.php?id=' + id)
            .then(response => response.json());
    }
};
