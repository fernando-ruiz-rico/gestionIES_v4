// Funciones para gestión de escenarios de desideratas

// Función para mostrar los datos del escenario seleccionado
function seleccionarEscenarioHistorico()
{
    var selEscenarioHistorico = $('#escenarioHistorico').value;
    if (selEscenarioHistorico <= 0)
    {
        $('#historico').style.display = "none";
    } else {
        $('#historico').load("ajax/escenarios/cargar_historico.php", {idEscenario: selEscenarioHistorico}, function()
        {
            $('#historico').style.display = "block";            
        });
    }
}

seleccionarEscenarioHistorico();