<?php

// Duplica los datos del escenario indicado. Esta duplicación consiste en:
// - Crear un escenario con el mismo nombre que el indicado y el sufijo "bis" (luego se puede editar dicho nombre)
// - Asignar al nuevo escenario los mismos departamentos que tenía el escenario original
// - Definir en el nuevo escenario las mismas selecciones de materias que había en el escenario original

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['idEscenario']))
{
    include ('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE id = " . $_REQUEST['idEscenario']);
    if ($fila = mysqli_fetch_assoc($result))
    {
        // Creamos el nuevo escenario, copia del actual (sufijo "bis")
        mysqli_query($db, "INSERT INTO escenarios_desideratas (nombre) VALUES ('" . $fila['nombre'] . " bis')");
        // Nos quedamos con el "id" del nuevo escenario para las siguientes operaciones
        $result2 = mysqli_query($db, "SELECT max(id) AS idMax FROM escenarios_desideratas WHERE nombre = '" . $fila['nombre'] . " bis'");
        if ($fila2 = mysqli_fetch_assoc($result2))
        {
            // Asignamos al nuevo escenario los mismos departamentos que tenía el original
            mysqli_query($db, "INSERT INTO departamentos_escenarios (idEscenario, idDepartamento) (SELECT " . $fila2['idMax'] . ", idDepartamento FROM departamentos_escenarios WHERE idEscenario = " . $_REQUEST['idEscenario'] . ")");
            // Asignamos también las mismas selecciones de materias que tuviera el escenario original
            mysqli_query($db, "INSERT INTO seleccion (idProfesor, idMateria, idGrupo, horas, orden, idEscenario) (SELECT idProfesor, idMateria, idGrupo, horas, orden, " . $fila2['idMax'] . " FROM seleccion WHERE idEscenario = " . $_REQUEST['idEscenario'] . ")");
        }
        mysqli_free_result($result2);
    } 
    mysqli_free_result($result);
    include ('../../includes/database2.php');
}

?>