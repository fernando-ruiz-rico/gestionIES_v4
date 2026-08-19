const pccfApartadosAPI = {
    listar() {
        return fetch('../backend/api/pccf_apartados/listar.php')
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
    },
    
    obtener(id) {
        return fetch('../backend/api/pccf_apartados/obtener.php?id=' + id)
            .then(response => response.json());
    },
    
    guardar(datos) {
        return fetch('../backend/api/pccf_apartados/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        }).then(response => response.json());
    },
    
    eliminar(id) {
        return fetch('../backend/api/pccf_apartados/eliminar.php?id=' + id)
            .then(response => response.json());
    }
};
