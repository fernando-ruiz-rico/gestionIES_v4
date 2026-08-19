<?php
/** Controlador seguro de vistas para el frontend Vue. */
define('GESTIONIES_FRAGMENT', TRUE);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$allowedPages = array(
    'index', 'actas', 'ayuda', 'ciclos', 'competencias_ciclos', 'configuracion',
    'cualificaciones_uc', 'cursos', 'departamentos', 'editar_tema', 'escenarios',
    'especialidades', 'estadisticas', 'grupos', 'historico', 'materias', 'pccf',
    'pccf_apartados', 'pccf_contenidos_defecto', 'profesores', 'programaciones',
    'programaciones_apartados', 'programaciones_aula', 'programaciones_contenidos_defecto',
    'programaciones_seguimiento', 'programaciones_seguimiento_aula',
    'programaciones_seguimiento_vista_previa', 'programaciones_vista_previa',
    'resultados_aprendizaje', 'resultados_aprendizaje_vista_previa', 'seleccion',
    'temas', 'temas_contenidos_defecto'
);

$page = isset($_GET['page']) ? $_GET['page'] : 'index';
$page = preg_replace('/[^a-z0-9_]/', '', strtolower($page));
if (!in_array($page, $allowedPages)) {
    if (function_exists('http_response_code')) http_response_code(404);
    else header('X-PHP-Response-Code: 404', true, 404);
    echo '<div class="alert alert-warning">La sección solicitada no existe.</div>';
    exit;
}

include(dirname(__FILE__) . '/' . $page . '.php');
?>
