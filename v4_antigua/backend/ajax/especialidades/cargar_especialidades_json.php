<?php

// Esta página se invoca desde la función "cargarPerfil" de "js/main.js" y devuelve el listado de
// especialidades del departamento que se solicita, en formato JSON

if (!empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $idDepartamento = $_REQUEST['idDepartamento'];
    $result = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = $idDepartamento ORDER BY id");
    $datos = array();
    while ($fila = mysqli_fetch_assoc($result))
    {
        $datos[] = array("id" => $fila['id'], "descripcion" => $fila['descripcion']);
    }
    echo json_encode($datos);
    mysqli_free_result($result);
    include ('../../includes/database2.php');
}


?>
