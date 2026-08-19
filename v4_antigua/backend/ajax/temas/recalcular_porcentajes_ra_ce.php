<?php
// -------------------------------
// Genera los checkboxes de resultados de aprendizaje y criterios de evaluación para una materia dada
// -------------------------------
function calcularPorcentajesRA($idMateria)
{
    $idMateria = (int)$idMateria;

    $sqlRA = "SELECT ra.id, ra.orden, COUNT(ct.codigo) AS num_criterios
                FROM resultados_aprendizaje ra
                LEFT JOIN criterios_temas ct ON ra.id = ct.idRA
                WHERE ra.idMateria = {$idMateria}
                GROUP BY ra.id, ra.orden
                ORDER BY ra.orden";
    $resultadosAprendizaje = consultarBaseDeDatos($sqlRA);

    if (empty($resultadosAprendizaje)) return;

    $sqlTotalCriterios = "SELECT COUNT(*) AS total_criterios
                            FROM resultados_aprendizaje ra
                            INNER JOIN criterios_temas ct ON ra.id = ct.idRA
                            WHERE ra.idMateria = {$idMateria}";
    $totalCriterios = consultarBaseDeDatos($sqlTotalCriterios);

    $totalCriterios = empty($totalCriterios) ? 0 : (int)$totalCriterios[0]['total_criterios'];

    $porcentajes = [];
    $suma = 0;

    // Calcular porcentajes enteros
    foreach ($resultadosAprendizaje as $ra) {
        $id = (int)$ra['id'];
        $numCriterios = (int)$ra['num_criterios'];
        $porcentaje = $totalCriterios > 0 ? (int)(($numCriterios / $totalCriterios) * 100) : 0;

        $porcentajes[] = [
            'id' => $id,
            'porcentaje' => $porcentaje
        ];
        $suma += $porcentaje;
    }

    // Si la suma no llega a 100, sumar 1 a los últimos con porcentaje > 0
    if ($suma > 0 && $suma < 100) {
        for ($i = count($porcentajes) - 1; $i >= 0 && $suma < 100; $i--) {
            if ($porcentajes[$i]['porcentaje'] > 0) {
                $porcentajes[$i]['porcentaje']++;
                $suma++;
            }
        }
    }

    // Actualizar en BD
    foreach ($porcentajes as $item) {
        $sqlRAUpdate = "UPDATE resultados_aprendizaje
                            SET porcentaje_evaluacion = {$item['porcentaje']}
                            WHERE id = {$item['id']} AND idMateria = {$idMateria}";
        actualizarBaseDeDatos($sqlRAUpdate);
    }
}

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idMateria']))
{
    require_once('../../includes/database.php');
    require_once('../../includes/utilidades.php');
    calcularPorcentajesRA($_REQUEST['idMateria']);
    require_once('../../includes/database2.php');
}
?>