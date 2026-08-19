// Funciones para gestionar los contenidos de las programaciones

// Variables para almacenar materia y apartado a editar
let selMateria = 0;
let selApartado = 0;
let tipoApartado = 0;
let editorTinyMCE = null;

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = document.getElementById('materia').value;
    document.querySelector('input[name="idMateria"]').value = selMateria;
    selApartado = 0;
    document.getElementById('apartado').value = selApartado;
    document.getElementById('edicionapartado').style.display = 'none';
    if (editorTinyMCE)
        editorTinyMCE.setContent("");

    // Actualizamos los apartados según el tipo de materia elegida
    const apartadoSelect = document.getElementById('apartado');
    apartadoSelect.disabled = true;
    apartadoSelect.innerHTML = '<option value="0">Cargando…</option>';
    
    fetch('ajax/programaciones/cargar_apartados.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idMateria=${selMateria}`
    })
    .then(response => response.json())
    .then(res => {
        let opciones = '<option value="0">--Selecciona un apartado--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id},${a.tipo}">${a.nombre}</option>`;
        });
        apartadoSelect.innerHTML = opciones;
        apartadoSelect.disabled = false;   
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje("Error al cargar los apartados", 0);
    });
}

// Cambia el apartado seleccionado
function cambiarApartado()
{
    if (editorTinyMCE)
        editorTinyMCE.setContent("");

    const valor = document.getElementById('apartado').value.split(',');
    selApartado = parseInt(valor[0]) || 0;
    tipoApartado = parseInt(valor[1]) || 0;

    if (tipoApartado == 0) {
        document.getElementById('idApartado').value = selApartado;
        if (selMateria > 0 && selApartado > 0) {
            fetch('ajax/programaciones/cargar_contenido_programacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `idMateria=${selMateria}&idApartado=${selApartado}`
            })
            .then(response => response.text())
            .then(res => {
                document.getElementById('edicionapartado').style.display = 'block';
                document.getElementById('mensajeapartadoautomatico').style.display = 'none';
                if (editorTinyMCE)
                    editorTinyMCE.setContent(res);
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje("Error al cargar el contenido", 0);
            });
        }
    }
    else {
        document.getElementById('edicionapartado').style.display = 'none';
        document.getElementById('mensajeapartadoautomatico').style.display = 'block';
    }
}

// Genera una vista previa en HTML de la programación
function vistaPreviaProgramacion()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('programaciones_vista_previa.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de la programación
function generarPDF()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('pdf_programaciones.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de las unidades de programación
function generarPDFUnidades()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de un apartado en concreto
function generarPDFApartado()
{
    if (selMateria <= 0 || selApartado <= 0) {
        mostrarMensaje("Debes seleccionar una materia y un apartado", 2);
    }
    else {
        // Si el apartado es de temas, no se genera el PDF desde aquí
        if (tipoApartado == TIPO_APARTADO_TEMAS) {
            window.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
        }
        else {
            window.open('pdf_programaciones_apartado.php?idMateria=' + selMateria + '&idApartado=' + selApartado);
        }
    }
}

// Importa otra programación en la materia seleccionada
async function importarProgramacion()
{
    if(selMateria <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia", 2);
    }
    else
    {
        if (await confirmar("Al importar una programación se borrarán TODOS los contenidos de la programación para la materia actualmente seleccionada. ¿Deseas continuar?"))
        {
            document.getElementById('idMateriaDestino').value = selMateria;
            const modal = new bootstrap.Modal(document.getElementById('formimportarprog'));
            modal.show();
        }        
    }
}

// Carga otra pestaña para editar el contenido por defecto de las unidades o temas
function contenidoDefectoTemas()
{
    window.open('temas_contenidos_defecto.php');
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar temas
    const formTemas = document.getElementById('temas');
    if (formTemas) {
        formTemas.addEventListener('submit', function(e)
        {
            if(selMateria <= 0)
            {
                mostrarMensaje("Debes seleccionar una materia", 2);
                e.preventDefault();
            }
            else
            {
                document.getElementById('idMateria').value = selMateria;
            }
        });
    }

    // Guardar cambios al contenido editado
    const formProgramacion = document.getElementById('formprogramacion');
    if (formProgramacion) {
        formProgramacion.addEventListener('submit', function(e)
        {
            e.preventDefault();
            if (editorTinyMCE)
                editorTinyMCE.save();
            
            if (selApartado <= 0 || selMateria <= 0)
                mostrarMensaje("Debes seleccionar una materia y un apartado", 2);
            else
            {
                var formData = new FormData(formProgramacion);

                fetch("ajax/programaciones/insertar_contenido_programacion.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(res => {
                    if (res.trim() == 'si')
                        mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
                    else
                        mostrarMensaje("Datos guardados correctamente", 1);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje("Error al guardar los datos", 0);
                });
            }
        });
    }

    // Guardar cambios al contenido editado - formulario de importación
    const formImpProg = document.getElementById('formimpprog');
    if (formImpProg) {
        formImpProg.addEventListener('submit', function(e)
        {
            e.preventDefault();
            var formData = new FormData(formImpProg);
            
            fetch("ajax/programaciones/importar_programacion.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(res => {
                document.getElementById('idMateriaOrigen').value = "";
                document.getElementById('idMateriaDestino').value = "";
                const modalEl = document.getElementById('formimportarprog');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                document.getElementById('edicionapartado').style.display = 'none';
                mostrarMensaje("Operación completada", 1);
                cambiarApartado();
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje("Error al importar la programación", 0);
            });
        });
    }

    // Configuración de TinyMCE si procede
    if(document.getElementById('edicionapartado'))
    {
        if (typeof initTinyMCE === 'function') {
            initTinyMCE('progeditar');
            // Esperar a que TinyMCE se inicialice
            setTimeout(function() {
                editorTinyMCE = tinymce.get('texto');
            }, 500);
        }

        // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
        // Sólo se mostrará si elegimos un apartado concreto del listado
        document.getElementById('edicionapartado').style.display = 'none';
    }
});
