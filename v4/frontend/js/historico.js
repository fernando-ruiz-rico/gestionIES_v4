// Funciones para gestión de escenarios de desideratas

// Función para mostrar los datos del escenario seleccionado
function seleccionarEscenarioHistorico()
{
    var selEscenarioHistorico = dom('#escenarioHistorico').val();
    if (selEscenarioHistorico <= 0)
    {
        dom('#historico').hide();
    } else {
        dom('#historico').load("ajax/escenarios/cargar_historico.php", {idEscenario: selEscenarioHistorico}, function()
        {
            dom('#historico').show();            
        });
    }
}

seleccionarEscenarioHistorico();