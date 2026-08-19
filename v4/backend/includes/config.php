<?php

/*
Aquí se almacenan los elementos del menú de la aplicación. Cada elemento del array significa lo siguiente:
    - "id": Identificador del menú. Los elementos que comparten "id" es porque son submenús de uno principal
    - "submenu": a FALSE indica que es un menú principal, a TRUE es un submenú
    - "texto": texto que se ve en el menú
    - "roles": roles que tienen acceso a ese menú, o NULL para que lo vea cualquiera logueado
       roles disponibles: admin, jefeDepartamento, profesor
    - "icono": icono que aparece junto al texto del menú
    - "link": URL que se abre al elegir el menú, o NULL en el caso de links de submenu ("submenu"=FALSE)
*/

@session_start();

$departamentoUsuario = 0;
if(isset($_SESSION['departamentoUsuario']))
    $departamentoUsuario = $_SESSION['departamentoUsuario'];

// Los admins y jefes de departamento van a poder acceder a opciones de configuración deshabilitadas para el resto
$super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');

$menus = array(
    array("id" => 1, "submenu" => FALSE, "texto" => "Profesores y Departamentos", "roles" => array("admin"), "icono" => "teacher", "link" => NULL), 
    array("id" => 1, "submenu" => TRUE, "texto" => "Departamentos", "roles" => array("admin"), "icono" => "archive", "link" => "departamentos.php"),
    array("id" => 1, "submenu" => TRUE, "texto" => "Especialidades", "roles" => array("admin"), "icono" => "tree2", "link" => "especialidades.php"),
    array("id" => 1, "submenu" => TRUE, "texto" => "Profesores", "roles" => array("admin"), "icono" => "teacher", "link" => "profesores.php"),
    array("id" => 2, "submenu" => FALSE, "texto" => "Cursos y Materias", "roles" => array("admin"), "icono" => "tree", "link" => NULL), 
    array("id" => 2, "submenu" => TRUE, "texto" => "Ciclos", "roles" => array("admin"), "icono" => "education", "link" => "ciclos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Cursos", "roles" => array("admin"), "icono" => "tree", "link" => "cursos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Grupos", "roles" => array("admin"), "icono" => "conflicts", "link" => "grupos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Materias", "roles" => array("admin"), "icono" => "printall", "link" => "materias.php"),
    array("id" => 3, "submenu" => FALSE, "texto" => "Programaciones", "roles" => NULL, "icono" => "document", "link" => NULL), 
    array("id" => 3, "submenu" => TRUE, "texto" => "Apartados PD", "roles" => array("admin"), "icono" => "list", "link" => "programaciones_apartados.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Contenidos generales", "roles" => array("admin", "jefeDepartamento"), "icono" => "backup", "link" => "programaciones_contenidos_defecto.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Formación Empresa (RA)", "roles" => NULL, "icono" => "results", "link" => "resultados_aprendizaje.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Competencias", "roles" => array("admin"), "icono" => "capability", "link" => "competencias_ciclos.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Cualificaciones y UC", "roles" => array("admin"), "icono" => "qualification", "link" => "cualificaciones_uc.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Programaciones", "roles" => NULL, "icono" => "document", "link" => "programaciones.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Programaciones de aula", "roles" => NULL, "icono" => "classroom", "link" => "programaciones_aula.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Apartados PCCF", "roles" => array("admin"), "icono" => "list", "link" => "pccf_apartados.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Contenidos grales. PCCF", "roles" => array("admin", "jefeDepartamento"), "icono" => "backup", "link" => "pccf_contenidos_defecto.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "PCCF", "roles" => NULL, "icono" => "document", "link" => "pccf.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Seguimiento", "roles" => NULL, "icono" => "tracking", "link" => "programaciones_seguimiento_aula.php"),
    array("id" => 4, "submenu" => FALSE, "texto" => "Desideratas", "roles" => NULL, "icono" => "hand", "link" => NULL), 
    array("id" => 4, "submenu" => TRUE, "texto" => "Escenarios", "roles" => array("admin", "jefeDepartamento"), "icono" => "paths", "link" => "escenarios.php"),
    array("id" => 4, "submenu" => TRUE, "texto" => "Histórico", "roles" => array("profesor", "jefeDepartamento"), "icono" => "history", "link" => "historico.php"),
    // La variable $desideratasActivadas se crea en includes/comprobar_activaciones.php, invocado en cabecera.php
    array("id" => 4, "submenu" => TRUE, "texto" => "Selección", "roles" => NULL, "icono" => "hand", "link" => ($super || $desideratasActivadas)?"seleccion.php":"javascript:mostrarMensaje('Opción deshabilitada en este momento', 0)"),
    array("id" => 5, "submenu" => FALSE, "texto" => "Actas", "roles" => NULL, "icono" => "book", "link" => "actas.php"),
    // Falta añadir aquí la sección de mensajes
    array("id" => 7, "submenu" => FALSE, "texto" => "Perfil", "roles" => array("profesor", "jefeDepartamento"), "icono" => "user", "link" => "javascript:cargarPerfil(" . (isset($_SESSION['idUsuario'])?$_SESSION['idUsuario']:0) . ", $departamentoUsuario, false)"),
    array("id" => 8, "submenu" => FALSE, "texto" => "Configuración", "roles" => array("admin"), "icono" => "settings", "link" => "configuracion.php"),
    array("id" => 9, "submenu" => FALSE, "texto" => "Ayuda", "roles" => NULL, "icono" => "help", "link" => "ayuda.php"),
    array("id" => 10, "submenu" => FALSE, "texto" => "Ayuda (Admin)", "roles" => array("admin"), "icono" => "help", "link" => "ayuda.php?admin=si"),
    array("id" => 11, "submenu" => FALSE, "texto" => "Salir", "roles" => NULL, "icono" => "logout", "link" => "logout.php")
);

?>