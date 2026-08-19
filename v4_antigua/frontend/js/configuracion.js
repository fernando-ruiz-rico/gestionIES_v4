// Función para activar/desactivar la opción de las programaciones en la configuración
dom('#btnprogramaciones').click(function()
{
    var valor = dom('#btnprogramaciones').text();
    GestionIES.navigate("configuracion.php?activarprogramaciones=si&valor=" + valor);
});

// Función para activar/desactivar la opción de las desideratas en la configuración
dom('#btndesideratas').click(function()
{
    var valor = dom('#btndesideratas').text();
    GestionIES.navigate("configuracion.php?activardesideratas=si&valor=" + valor);
});

// El formulario ya no se envía al documento del frontend: se procesa en el backend.
dom('#formconfig').on('submit', function(e)
{
    e.preventDefault();
    http.ajax({
        url: 'view.php?page=configuracion',
        type: 'post',
        data: new FormData(this)
    }).done(function()
    {
        GestionIES.reloadPage();
    });
});
        
