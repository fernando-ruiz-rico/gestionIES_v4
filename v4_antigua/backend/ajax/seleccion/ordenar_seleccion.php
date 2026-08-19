<?php

// Reordena las materias de la selección enviada. Esto cambia las prioridades de selección de un profesor
// para un conjunto de materias, priorizando unas frente a otras
// En el parámetro "orden" nos llevan las materias de la selección del profesor en una cadena de texto
// Troceamos esa cadena y vamos asignando a cada materia un orden consecutivo

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['orden']) && !empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');

    $idEscenario = $_REQUEST['idEscenario'];

    // Vemos si el escenario está en modo rueda o no, para deshabilitar ciertas acciones (elegir materias por profesores)
    $resultado = mysqli_query($db, "SELECT modo_rueda FROM escenarios_desideratas WHERE id = " . $idEscenario);
    $modoRueda = FALSE;
    while($fila = mysqli_fetch_assoc($resultado))
        $modoRueda = $fila['modo_rueda'];
    mysqli_free_result($resultado);

    // Guardamos si el usuario tiene permisos superiores (jefe de departamento o admin)
    $super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

    if(!$modoRueda || $super)
    {
        $orden = $_REQUEST['orden'];
        $partes = explode(",", $orden);
        for ($i = 1; $i <= count($partes); $i++)
        {
            $codSel = substr($partes[$i-1], 3);
            mysqli_query($db, "UPDATE seleccion SET orden=$i WHERE id=$codSel");
        }
    }
    include ('../../includes/database2.php');
}

?>