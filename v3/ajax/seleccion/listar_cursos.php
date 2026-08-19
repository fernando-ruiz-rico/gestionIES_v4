<?php

// Muesta un listado de los cursos disponibles para el escenario indicado

@session_start();

if(isset($_SESSION['departamentoUsuario']))
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

    // Si es jefe de departamento o admin muestra todos los cursos 
    if ($super)
        $resultado = mysqli_query($db, "SELECT cursos.id AS idCurso, cursos.nombre AS nombreCurso, grupos.id AS idGrupo, grupos.nombre AS nombreGrupo, grupos.mostrar FROM cursos, grupos WHERE cursos.id IN (SELECT idCurso FROM materias WHERE idDepartamento=" . $_SESSION['departamentoUsuario'] . ") AND grupos.idCurso = cursos.id ORDER BY cursos.orden, grupos.orden, grupos.nombre");
    // Si no, sólo muestra los cursos que no son asignados por la directiva (cargos, etc)
    else
        $resultado = mysqli_query($db, "SELECT cursos.id AS idCurso, cursos.nombre AS nombreCurso, grupos.id AS idGrupo, grupos.nombre AS nombreGrupo, grupos.mostrar FROM cursos, grupos WHERE cursos.id IN (SELECT idCurso FROM materias WHERE idDepartamento=" . $_SESSION['departamentoUsuario'] . " AND asignada_directiva = 0) AND grupos.idCurso = cursos.id ORDER BY cursos.orden, grupos.orden, grupos.nombre");
        
    while ($fila = mysqli_fetch_assoc($resultado))
    {
        $idCurso = $fila['idCurso'];
        $nombreCurso = $fila['nombreCurso'];
        $idGrupo = $fila['idGrupo'];
        $nombreGrupo = $fila['mostrar']?$fila['nombreGrupo']:'';

        echo '<div class="curso oscuro izquierda" id="cur' . $idCurso . '">';    
        echo "<strong>$nombreCurso $nombreGrupo</strong>";    
        echo '</div>';

        // Si tiene permisos, muestra todas las materias
        if ($super)
            $resultado2 = mysqli_query($db, "SELECT materias.id, materias.nombre, materias.divisible, materias.idEspecialidad, materias_grupos.cantidad, materias_grupos.horas, materias_grupos.min_num_profesores, materias_grupos.max_grupos_profesor FROM materias, materias_grupos WHERE materias.idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND materias.id = materias_grupos.idMateria AND materias_grupos.cantidad > 0 AND materias_grupos.idGrupo = $idGrupo AND materias.idCurso = $idCurso ORDER BY nombre");
        // Si no, sólo aquellas que no asigne la directiva
        else
            $resultado2 = mysqli_query($db, "SELECT materias.id, materias.nombre, materias.divisible, materias.idEspecialidad, materias_grupos.cantidad, materias_grupos.horas, materias_grupos.min_num_profesores, materias_grupos.max_grupos_profesor FROM materias, materias_grupos WHERE materias.idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND materias.id = materias_grupos.idMateria AND materias_grupos.cantidad > 0 AND materias_grupos.idGrupo = $idGrupo AND materias.idCurso = $idCurso AND asignada_directiva = 0 ORDER BY nombre");

        echo '<div class="materias">';
        while ($fila2 = mysqli_fetch_assoc($resultado2))
        {
            $idMateria = $fila2['id'];
            $nombreMateria = $fila2['nombre'];
            $horas = $fila2['horas'];
            $divisible = $fila2['divisible'];
            $cantidad = $fila2['cantidad'];
            $especialidadMateria = $fila2['idEspecialidad'];
            $minNumProfesores = $fila2['min_num_profesores'];
            $maxGruposProfesor = $fila2['max_grupos_profesor'];


            // Obtenemos cuántos profesores han seleccionado la materia
            $resultado3 = mysqli_query($db, "SELECT COUNT(*) AS total FROM seleccion WHERE idMateria = $idMateria AND idGrupo = $idGrupo AND idEscenario = $idEscenario");
            $fila3 = mysqli_fetch_assoc($resultado3);
            $total = $fila3['total'];
            mysqli_free_result($resultado3);

            // Mostramos un "badge" de un color u otro dependiendo de si hay menos, igual o más profesores de los necesarios
            if ($total > $cantidad)
                $clase = "badge bg-danger";
            else if ($total < $cantidad)
                $clase = "badge bg-warning";
            else 
                $clase = "badge bg-success";
            $datos = '<label class="' . $clase . '">' . $total . ' / ' . $cantidad . '</label>'; 

            // Mostramos un "popup" con mensaje informativo si la asignatura tiene restricciones de número de profesores o máximo de peticiones por profesor
            $info = "";
            if($minNumProfesores > 0 || $maxGruposProfesor > 0)
            {
                $minNumProfesores = $minNumProfesores > 0 ? $minNumProfesores : "-";
                $maxGruposProfesor = $maxGruposProfesor > 0 ? $maxGruposProfesor: "-";
                $info = '<label class="badge bg-info" onclick="mostrarMensaje(\'Esta asignatura necesita '. $minNumProfesores . ' profesores distintos, y cada uno puede elegir un máximo de ' . $maxGruposProfesor . ' grupo(s).\',3)">?</label>';
            }

            if($modoRueda && !$super)
                echo '<div class="materia izquierda claro"><div class=izquierda>'. $nombreMateria . ' (' . $horas . 'h)&nbsp;' . $info . '</div><div class="derecha" onclick="cargarSeleccionesMateria(' . $idMateria . "," . $idGrupo . "," . $idEscenario . ",'" . $nombreMateria . "'" . ",'" . $nombreCurso . "','" . $nombreGrupo . "')\">" . $datos . '</div></div>';
            else
                echo '<div class="materia izquierda claro"><div class=izquierda><button onclick="seleccionarHorasMateria(' . $idMateria . ", $idGrupo, '" . $especialidadMateria . "', " . $horas . ', ' . ($divisible?"true":"false") . ')"><img src="img/add.png" width="20" /></button>&nbsp;'. $nombreMateria . ' (' . $horas . 'h)&nbsp;' . $info . '</div><div class="derecha" onclick="cargarSeleccionesMateria(' . $idMateria . "," . $idGrupo . "," . $idEscenario . ",'" . $nombreMateria . "'" . ",'" . $nombreCurso . "','" . $nombreGrupo . "')\">" . $datos . '</div></div>';
        }
        echo '</div>';

        mysqli_free_result($resultado2);
    }

    mysqli_free_result($resultado);
    include ('../../includes/database2.php');
}
?>