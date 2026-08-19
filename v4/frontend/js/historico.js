// Funciones para gestión de escenarios de desideratas

// Función para mostrar los datos del escenario seleccionado
function seleccionarEscenarioHistorico()
{
    var selEscenarioHistorico = document.getElementById('escenarioHistorico').value;
    if (selEscenarioHistorico <= 0)
    {
        document.getElementById('historico').style.display = 'none';
    } else {
        document.getElementById('historico').load("ajax/escenarios/cargar_historico.php", {idEscenario: selEscenarioHistorico}, function()
        {
            document.getElementById('historico').style.display = 'block';            
        });
    }
}

seleccionarEscenarioHistorico();