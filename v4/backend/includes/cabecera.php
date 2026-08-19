<?php
/** Cabecera común. En modo fragmento sólo prepara sesión, permisos y datos. */
@session_start();
$fragmento = defined('GESTIONIES_FRAGMENT') && GESTIONIES_FRAGMENT;

if (empty($_SESSION['idUsuario'])) {
    if ($fragmento) {
        if (function_exists('http_response_code')) http_response_code(401);
        else header('X-PHP-Response-Code: 401', true, 401);
        echo '<div class="alert alert-warning">La sesión ha caducado.</div>';
    } else {
        header('Location: ../frontend/index.html');
    }
    exit;
}

if (isset($roles) && !in_array($_SESSION['rol'], $roles)) {
    if ($fragmento) {
        if (function_exists('http_response_code')) http_response_code(403);
        else header('X-PHP-Response-Code: 403', true, 403);
        echo '<div class="alert alert-danger">No tienes permisos para acceder a esta sección.</div>';
    } else {
        header('Location: ../frontend/index.html');
    }
    exit;
}

include('comprobar_activaciones.php');
include('config.php');

if (!empty($_REQUEST['idDepartamento'])) $_SESSION['departamentoUsuario'] = $_REQUEST['idDepartamento'];
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
$permisosAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($fragmento) {
    include('modales/mensaje.php');
    return;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión interna IESSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../frontend/css/estilos.css?v=4">
    <link rel="stylesheet" href="../frontend/css/estilos_programaciones.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/lib/js/tinymce/tinymce.min.js?v=7.9.1"></script>
    <script src="../frontend/js/dom.js?v=4"></script>
    <script src="../frontend/js/main.js?v=9"></script>
</head>
<body>
<div class="container-fluid py-3">
<?php include('modales/mensaje.php'); ?>
