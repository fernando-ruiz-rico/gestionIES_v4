// Función para activar/desactivar la opción de las programaciones en la configuración
document.getElementById('btnprogramaciones').click(function()
{
    var valor = document.getElementById('btnprogramaciones').textContent;
    window.location = "configuracion.php?activarprogramaciones=si&valor=" + valor;
});

// Función para activar/desactivar la opción de las desideratas en la configuración
document.getElementById('btndesideratas').click(function()
{
    var valor = document.getElementById('btndesideratas').textContent;
    window.location = "configuracion.php?activardesideratas=si&valor=" + valor;
});
        