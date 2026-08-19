<?php
/**
 * Configuración del menú de la aplicación
 */

@session_start();

$departamentoUsuario = 0;
if(isset($_SESSION['departamentoUsuario']))
    $departamentoUsuario = $_SESSION['departamentoUsuario'];

// Los admins y jefes de departamento van a poder acceder a opciones de configuración deshabilitadas para el resto
$super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');

// Comprobar activaciones
include('comprobar_activaciones.php');

$menus = array(
    array("id" => 1, "submenu" => FALSE, "texto" => "Profesores y Departamentos", "roles" => array("admin"), "icono" => "bi-person-badge", "link" => NULL), 
    array("id" => 1, "submenu" => TRUE, "texto" => "Departamentos", "roles" => array("admin"), "icono" => "bi-archive", "link" => "../frontend/departamentos.php"),
    array("id" => 1, "submenu" => TRUE, "texto" => "Especialidades", "roles" => array("admin"), "icono" => "bi-diagram-3", "link" => "../frontend/especialidades.php"),
    array("id" => 1, "submenu" => TRUE, "texto" => "Profesores", "roles" => array("admin"), "icono" => "bi-person-badge", "link" => "../frontend/profesores.php"),
    array("id" => 2, "submenu" => FALSE, "texto" => "Cursos y Materias", "roles" => array("admin"), "icono" => "bi-tree", "link" => NULL), 
    array("id" => 2, "submenu" => TRUE, "texto" => "Ciclos", "roles" => array("admin"), "icono" => "bi-mortarboard", "link" => "../frontend/ciclos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Cursos", "roles" => array("admin"), "icono" => "bi-tree", "link" => "../frontend/cursos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Grupos", "roles" => array("admin"), "icono" => "bi-people", "link" => "../frontend/grupos.php"),
    array("id" => 2, "submenu" => TRUE, "texto" => "Materias", "roles" => array("admin"), "icono" => "bi-journal-text", "link" => "../frontend/materias.php"),
    array("id" => 3, "submenu" => FALSE, "texto" => "Programaciones", "roles" => NULL, "icono" => "bi-file-text", "link" => NULL), 
    array("id" => 3, "submenu" => TRUE, "texto" => "Apartados PD", "roles" => array("admin"), "icono" => "bi-list", "link" => "../frontend/programaciones_apartados.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Contenidos generales", "roles" => array("admin", "jefeDepartamento"), "icono" => "bi-database", "link" => "../frontend/programaciones_contenidos_defecto.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Formación Empresa (RA)", "roles" => NULL, "icono" => "bi-bar-chart", "link" => "../frontend/resultados_aprendizaje.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Competencias", "roles" => array("admin"), "icono" => "bi-star", "link" => "../frontend/competencias_ciclos.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Cualificaciones y UC", "roles" => array("admin"), "icono" => "bi-award", "link" => "../frontend/cualificaciones_uc.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Programaciones", "roles" => NULL, "icono" => "bi-file-text", "link" => "../frontend/programaciones.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Programaciones de aula", "roles" => NULL, "icono" => "bi-house-door", "link" => "../frontend/programaciones_aula.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Apartados PCCF", "roles" => array("admin"), "icono" => "bi-list", "link" => "../frontend/pccf_apartados.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Contenidos grales. PCCF", "roles" => array("admin", "jefeDepartamento"), "icono" => "bi-database", "link" => "../frontend/pccf_contenidos_defecto.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "PCCF", "roles" => NULL, "icono" => "bi-file-text", "link" => "../frontend/pccf.php"),
    array("id" => 3, "submenu" => TRUE, "texto" => "Seguimiento", "roles" => NULL, "icono" => "bi-clock-history", "link" => "../frontend/programaciones_seguimiento_aula.php"),
    array("id" => 4, "submenu" => FALSE, "texto" => "Desideratas", "roles" => NULL, "icono" => "bi-hand-index", "link" => NULL), 
    array("id" => 4, "submenu" => TRUE, "texto" => "Escenarios", "roles" => array("admin", "jefeDepartamento"), "icono" => "bi-signpost-split", "link" => "../frontend/escenarios.php"),
    array("id" => 4, "submenu" => TRUE, "texto" => "Histórico", "roles" => array("profesor", "jefeDepartamento"), "icono" => "bi-clock", "link" => "../frontend/historico.php"),
    array("id" => 4, "submenu" => TRUE, "texto" => "Selección", "roles" => NULL, "icono" => "bi-hand-index", "link" => ($super || $desideratasActivadas)?"../frontend/seleccion.php":"javascript:mostrarMensaje('Opción deshabilitada en este momento', 0)"),
    array("id" => 5, "submenu" => FALSE, "texto" => "Actas", "roles" => NULL, "icono" => "bi-book", "link" => "../frontend/actas.php"),
    array("id" => 7, "submenu" => FALSE, "texto" => "Perfil", "roles" => array("profesor", "jefeDepartamento"), "icono" => "bi-person", "link" => "javascript:cargarPerfil(" . (isset($_SESSION['idUsuario'])?$_SESSION['idUsuario']:0) . ", $departamentoUsuario, false)"),
    array("id" => 8, "submenu" => FALSE, "texto" => "Configuración", "roles" => array("admin"), "icono" => "bi-gear", "link" => "../frontend/configuracion.php"),
    array("id" => 9, "submenu" => FALSE, "texto" => "Ayuda", "roles" => NULL, "icono" => "bi-question-circle", "link" => "../frontend/ayuda.php"),
    array("id" => 10, "submenu" => FALSE, "texto" => "Ayuda (Admin)", "roles" => array("admin"), "icono" => "bi-question-circle", "link" => "../frontend/ayuda.php?admin=si"),
    array("id" => 11, "submenu" => FALSE, "texto" => "Salir", "roles" => NULL, "icono" => "bi-box-arrow-right", "link" => "../backend/logout.php")
);

?>
