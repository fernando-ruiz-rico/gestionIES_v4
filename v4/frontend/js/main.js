// Funciones de uso general en varios ficheros JavaScript

// Función para mostrar el modal modales/mensaje.php con un mensaje de acierto o error
function mostrarMensaje(mensaje, tipo)
{
    const textoMensajeModal = document.getElementById('textoMensajeModal');
    if (!textoMensajeModal) return;
    
    if (tipo == 0) { 
        textoMensajeModal.className = 'alert alert-danger';
    } else if (tipo == 1) {
        textoMensajeModal.className = 'alert alert-success';
    } else if (tipo == 2) {
        textoMensajeModal.className = 'alert alert-warning';        
    } else {
        textoMensajeModal.className = 'alert alert-light';       
    } 
    textoMensajeModal.innerHTML = mensaje;
    
    const mensajemodal = document.getElementById('mensajemodal');
    if (mensajemodal) {
        const modal = bootstrap.Modal.getInstance(mensajemodal) || new bootstrap.Modal(mensajemodal);
        modal.style.display = 'block';
    }
}

// Función para confirmar una acción con un mensaje personalizado
async function confirmar(mensaje, titulo = '¿Estás seguro?') 
{
    return Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: "btn btn-outline-danger me-2",
            cancelButton: "btn btn-outline-secondary"
        },
        buttonsStyling: false
    }).then(result => result.isConfirmed);
}

// Función auxiliar para cargar HTML desde una URL e insertarlo en un elemento
async function cargarHTML(url, params = {}, targetSelector)
{
    const formData = new URLSearchParams(params);
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        const html = await response.textContent;
        const target = document.querySelector(targetSelector);
        if (target) target.innerHTML = html;
        return html;
    } catch (error) {
        console.error('Error al cargar HTML:', error);
        return null;
    }
}

// Función auxiliar para hacer peticiones GET y devolver JSON
async function getJSON(url, params = {})
{
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? url + '?' + queryString : url;
    try {
        const response = await fetch(fullUrl);
        return await response.json();
    } catch (error) {
        console.error('Error en GET JSON:', error);
        return null;
    }
}

// Función auxiliar para hacer peticiones GET y devolver texto
async function getText(url, params = {})
{
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? url + '?' + queryString : url;
    try {
        const response = await fetch(fullUrl);
        return await response.textContent;
    } catch (error) {
        console.error('Error en GET text:', error);
        return null;
    }
}

// Función que muestra en el div "listaprofesores" los profesores del departamento seleccionado
function cargarProfesores(selDepartamento)
{
    cargarHTML('ajax/profesores/cargar_profesores.php', {idDepartamento: selDepartamento}, "#listaprofesores");
}

// Función para cargar en el DOM el modal del formulario para crear un profesor o editar su perfil
async function cargarModalProfesor(idDepartamento) 
{
    let formprofesor = document.getElementById('formprofesor');
    if (!formprofesor) {
        const modal = await getText('modales/profesores.php');
        document.body.insertAdjacentHTML('beforeend', modal);
        
        const formprof = document.getElementById('formprof');
        if (formprof) {
            formprof.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(formprof);
                fetch('ajax/profesores/insertar_profesor.php', { method: 'POST', body: formData })
                .then(response => response.textContent)
                .then(res => {
                    const modalElement = document.getElementById('formprofesor');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.style.display = 'none';
                    }
                    if (res.trim().startsWith('si'))
                        mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
                    else
                        mostrarMensaje("Operación realizada correctamente", 1);
                    if (typeof cargarProfesores !== 'undefined')
                        cargarProfesores(idDepartamento);
                })
                .catch(error => {
                    console.error('Error al guardar profesor:', error);
                    mostrarMensaje("Error al guardar el profesor", 0);
                });
            });
        }
    }

    const idDepartamentoPerfil = document.getElementById('idDepartamentoPerfil');
    if (!idDepartamentoPerfil || idDepartamentoPerfil.value != idDepartamento) {
        const resEsp = await getJSON("ajax/especialidades/cargar_especialidades_json.php", {idDepartamento:idDepartamento});
        if (resEsp) {
            const idEspecialidadPerfil = document.getElementById('idEspecialidadPerfil');
            if (idEspecialidadPerfil) {
                idEspecialidadPerfil.innerHTML = '';
                for(var i = 0; i < resEsp.length; i++) {
                    const option = document.createElement('option');
                    option.value = resEsp[i].id;
                    option.textContent = resEsp[i].descripcion;
                    idEspecialidadPerfil.appendChild(option);
                }
            }
            if (idDepartamentoPerfil) idDepartamentoPerfil.value = idDepartamento;
        }
    }
}

// Carga la ventana modal "modales/profesor.php" para editar los datos de un profesor
async function cargarPerfil(idProf, idDep, editarAbreviatura = true)
{
    await cargarModalProfesor(idDep);
    const res = await getJSON("ajax/profesores/cargar_profesor.php", {idProfesor:idProf});
    if (res) {
        const campos = {
            'idPerfil': idProf,
            'nombrePerfil': res.nombre,
            'abreviaturaPerfil': res.abreviatura,
            'usuarioPerfil': res.usuario,
            'clavePerfil': "",
            'telefonoPerfil': res.telefono,
            'emailPerfil': res.email,
            'idEspecialidadPerfil': res.idEspecialidad,
            'observacionesPerfil': res.observaciones_horario
        };
        for (const [id, valor] of Object.entries(campos)) {
            const el = document.getElementById(id);
            if (el) el.value = valor;
        }
        
        cargarHTML('ajax/profesores/cargar_preferencias_profesor.php', {idProfesor:idProf}, '#prefhoras');
        
        const abreviaturaPerfil = document.getElementById('abreviaturaPerfil');
        if (abreviaturaPerfil) {
            abreviaturaPerfil.readOnly = !editarAbreviatura;
        }

        const formprofesor = document.getElementById('formprofesor');
        if (formprofesor) {
            const modal = bootstrap.Modal.getInstance(formprofesor) || new bootstrap.Modal(formprofesor);
            modal.style.display = 'block';
        }
    }
}

// Función para preferencias de horario
function preferencia(id, tipo)
{
    const prefRojas = document.getElementById('prefRojas');
    const prefAmarillas = document.getElementById('prefAmarillas');
    if (!prefRojas || !prefAmarillas) return;
    
    var rojas = prefRojas.value.replace(id, "");
    var amarillas = prefAmarillas.value.replace(id, "");
    
    if (tipo == 1) rojas = rojas + id;
    if (tipo == 2) amarillas = amarillas + id;
    
    prefRojas.value = rojas;
    prefAmarillas.value = amarillas;    
}

// Función para recargar la página actual con el departamento seleccionado
function seleccionarDepartamento()
{
    const seleccionDepartamento = document.getElementById('seleccionDepartamento');
    if (seleccionDepartamento && seleccionDepartamento.value != "") {
        const pagina = window.location.pathname;
        window.location.href = pagina + "?idDepartamento=" + seleccionDepartamento.value;
    }
}

// Función para inicializar TinyMCE
function initTinyMCE(selector, height = 300) {
  return tinymce.init({
    selector: "textarea." + selector,
    height: height,
    resize: true,
    plugins: 'autolink lists advlist code fullscreen wordcount',
    toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
    statusbar: true,
    menubar: false,
    branding: false,
    content_css: 'css/estilos_tiny.css'
  });
}
