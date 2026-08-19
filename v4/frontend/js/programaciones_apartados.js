// Funciones para la gestión de los apartados de las programaciones didácticas

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    fetch('ajax/programaciones_apartados/cargar_apartados.php')
    .then(response => response.text())
    .then(html => {
        document.getElementById('apartadosprog').innerHTML = html;
        inicializarSortable();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Inicializa drag & drop para ordenar apartados
function inicializarSortable()
{
    const contenedor = document.getElementById('apartadosprog');
    if (!contenedor) return;
    
    const items = contenedor.querySelectorAll('.apartado');
    let draggedItem = null;
    
    items.forEach(item => {
        item.setAttribute('draggable', 'true');
        
        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            setTimeout(() => this.style.opacity = '0.5', 0);
        });
        
        item.addEventListener('dragend', function(e) {
            setTimeout(() => this.style.opacity = '1', 0);
            draggedItem = null;
            
            // Obtener el nuevo orden después de soltar
            const elementos = Array.from(contenedor.querySelectorAll('.apartado'))
                .map(el => el.id)
                .toString();
            
            fetch(`ajax/programaciones_apartados/ordenar_apartados.php?orden=${elementos}`)
            .then(() => {
                cargarApartados();
            })
            .catch(error => {
                console.error('Error al ordenar:', error);
            });
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(contenedor, e.clientY);
            if (afterElement == null) {
                contenedor.appendChild(draggedItem);
            } else {
                contenedor.insertBefore(draggedItem, afterElement);
            }
        });
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.apartado:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    fetch(`ajax/programaciones_apartados/cargar_apartado.php?idApartado=${id}`)
    .then(response => response.json())
    .then(res => {
        document.getElementById('idApartado').value = id;
        document.getElementById('titulo').value = res.titulo;
        document.getElementById('categoria').value = res.categoria;
        document.getElementById('tipo').value = res.tipo;
        document.getElementById('subapartado').checked = (res.subapartado == 1);
        document.getElementById('requerido').checked = (res.requerido == 1);
        document.getElementById('contenidoDefecto').checked = (res.contenido_defecto == 1);
        
        const modal = new bootstrap.Modal(document.getElementById('formapartadoprogramacion'));
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje("Error al cargar el apartado", 0);
    });
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    const modal = new bootstrap.Modal(document.getElementById('formapartadoprogramacion'));
    modal.show();
}

// Borra un apartado, previa confirmación
function borrarApartado(id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos de las programaciones relativos a dicho apartado."))
    {
        fetch('ajax/programaciones_apartados/borrar_apartado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.text())
        .then(res => {
            cargarApartados();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje("Error al borrar el apartado", 0);
        });
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    document.getElementById('idApartado').value = "";
    document.getElementById('titulo').value = "";
    document.getElementById('categoria').value = "";
    document.getElementById('tipo').value = "";
    document.getElementById('subapartado').checked = false;
    document.getElementById('requerido').checked = true;
    document.getElementById('contenidoDefecto').checked = false;
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Evento de envío del formulario modal para crear/modificar apartados
    const formApartado = document.getElementById('formapartado');
    if (formApartado) {
        formApartado.addEventListener('submit', function(e)
        {
            e.preventDefault();
            var formData = new FormData(formApartado);
            
            fetch("ajax/programaciones_apartados/insertar_apartado.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(res => {
                limpiarFormularioApartados();
                const modalEl = document.getElementById('formapartadoprogramacion');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                cargarApartados();
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje("Error al guardar el apartado", 0);
            });
        });
    }
    
    cargarApartados();
});
