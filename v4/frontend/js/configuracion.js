// Función para activar/desactivar la opción de las programaciones en la configuración
$('#btnprogramaciones').click(function()
{
    var valor = $('#btnprogramaciones').textContent;
    window.location = "configuracion.php?activarprogramaciones=si&valor=" + valor;
});

// Función para activar/desactivar la opción de las desideratas en la configuración
$('#btndesideratas').click(function()
{
    var valor = $('#btndesideratas').textContent;
    window.location = "configuracion.php?activardesideratas=si&valor=" + valor;
});
        