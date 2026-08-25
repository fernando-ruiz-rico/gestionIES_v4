<?php
// Utilidades compartidas de los endpoints de Resultados de Aprendizaje
// (api/resultados_aprendizaje/). Cada acción es un fichero, pero todas
// comparten estos permisos y estas consultas, así que viven aquí y no se
// duplican en cada endpoint.
//
// Convenio: la sesión ya debe estar iniciada (cada endpoint hace
// @session_start() antes de usarlas).

// Permisos de edición: admin o jefe de departamento
function raTienePermisoEdicion()
{
    return in_array($_SESSION['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));
}

// Un usuario solo puede trabajar sobre los RA de su propio departamento cuando
// es jefe de departamento; el admin puede sobre cualquier departamento.
function raPuedeTrabajarSobreDepartamento($idDepartamento)
{
    if ($_SESSION['rol'] == ROLE_ADMIN) {
        return true;
    }
    // Jefe solo puede editar si el departamento coincide con el suyo
    if (isset($_SESSION['departamentoUsuario'])) {
        return intval($_SESSION['departamentoUsuario']) == intval($idDepartamento);
    }
    return false;
}

// Devuelve el departamento del que depende la materia indicada (0 si no existe)
function raIdDepartamentoDeMateria($db, $idMateria)
{
    $fila = $db->fetchOne("SELECT idDepartamento FROM materias WHERE id = ?", $idMateria);
    return ($fila && $fila['idDepartamento'] !== null) ? intval($fila['idDepartamento']) : 0;
}

// Devuelve el departamento de la materia a la que pertenece un resultado
function raIdDepartamentoDeRA($db, $idResultado)
{
    $fila = $db->fetchOne("SELECT idMateria FROM resultados_aprendizaje WHERE id = ?", $idResultado);
    return ($fila) ? raIdDepartamentoDeMateria($db, intval($fila['idMateria'])) : 0;
}

// Comprueba que el usuario puede editar datos de una materia/departamento:
// los jefes de departamento solo sobre la materia de su propio departamento
// (el admin puede sobre cualquiera), como en la vista de v3.
function raComprobarDepartamento($idDepartamento)
{
    if (!$idDepartamento || !raPuedeTrabajarSobreDepartamento($idDepartamento)) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }
}
