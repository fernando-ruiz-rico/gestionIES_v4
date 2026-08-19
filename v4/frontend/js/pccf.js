// Funciones para gestionar los contenidos de los PCCF

// Variables para almacenar ciclo y apartado a editar
var selCiclo = 0;
var selApartado = 0;

// Cambia el ciclo seleccionado
function cambiarCiclo()
{    
    selCiclo = document.getElementById('ciclo').value;
    document.getElementById('apartado').value = '';
    selApartado = 0;
    dom('#idCiclo').val(selCiclo);
    dom('#idApartado').val(selApartado);
    dom('#edicionapartado').hide();
   if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
}

// Cambia el apartado seleccionado
function cambiarApartado()
{
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
    selApartado = dom('#apartado').val();
    dom('#idApartado').val(selApartado);
    if (selCiclo > 0 && selApartado > 0)
        fetch("ajax/pccf/cargar_contenido_pccf.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo: selCiclo, idApartado: selApartado}).toString() }).then(r => r.text()).then(res => {
            document.getElementById('edicionapartado').style.display = 'block';
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
}

// Genera un PDF con el contenido de la programación
function generarPDF()
{
    if (selCiclo <= 0)
        mostrarMensaje("Debes seleccionar un ciclo", 2);
    else
        GestionIES.open('pdf_pccf.php?idCiclo=' + selCiclo);
}

// Genera un PDF con el contenido de un apartado en concreto
function generarPDFApartado()
{
    if (selCiclo <= 0 || selApartado <= 0)
        mostrarMensaje("Debes seleccionar un ciclo y un apartado", 2);
    else
        GestionIES.open('pdf_pccf_apartado.php?idCiclo=' + selCiclo + '&idApartado=' + selApartado);
}

// Guardar cambios al contenido editado
document.getElementById("formpccf").addEventListener("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selApartado <= 0 || selCiclo <= 0)
        mostrarMensaje("Debes seleccionar un ciclo y un apartado", 2);
    else
    {
        var formData = new FormData(document.forms.formpccf);
        fetch("ajax/pccf/insertar_contenido_pccf.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

// Configuración de TinyMCE si procede
if(dom('#edicionapartado').length > 0)
{ 
    initTinyMCE('progeditar', 400);

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    dom('#edicionapartado').hide();
}
