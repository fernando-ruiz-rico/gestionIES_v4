// Funciones para gestión de escenarios de desideratas

// Función para mostrar los datos del escenario seleccionado
function seleccionarEscenarioHistorico()
{
    var selEscenarioHistorico = document.getElementById('escenarioHistorico').value;
    if (selEscenarioHistorico <= 0)
    {
        document.getElementById('historico').style.display = "none";
    } else {
        fetch('ajax/escenarios/cargar_historico.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'idEscenario=' + selEscenarioHistorico
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('historico').innerHTML = data;
            document.getElementById('historico').style.display = "block";
        })
        .catch(error => console.error('Error:', error));
    }
}

document.addEventListener("DOMContentLoaded", function() {
    seleccionarEscenarioHistorico();
});