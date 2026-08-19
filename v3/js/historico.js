// Funciones para gestión de escenarios de desideratas

// Función para mostrar los datos del escenario seleccionado
function seleccionarEscenarioHistorico()
{
    var selEscenarioHistorico = $('#escenarioHistorico').val();
    if (selEscenarioHistorico <= 0)
    {
        $('#historico').hide();
    } else {
        $('#historico').load("ajax/escenarios/cargar_historico.php", {idEscenario: selEscenarioHistorico}, function()
        {
            $('#historico').show();            
        });
    }
}

seleccionarEscenarioHistorico();