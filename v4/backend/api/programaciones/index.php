<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

try {
    switch ($accion) {
        case 'listar':
            $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
            
            if ($idMateria > 0) {
                $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                        FROM programaciones p 
                        LEFT JOIN materias m ON p.idMateria = m.id 
                        LEFT JOIN grupos g ON p.idGrupo = g.id 
                        WHERE p.idMateria = $idMateria 
                        ORDER BY p.curso DESC";
            } else {
                $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                        FROM programaciones p 
                        LEFT JOIN materias m ON p.idMateria = m.id 
                        LEFT JOIN grupos g ON p.idGrupo = g.id 
                        ORDER BY p.curso DESC";
            }
            
            $result = mysql_query($sql);
            $programaciones = [];
            
            if ($result) {
                while ($row = mysql_fetch_assoc($result)) {
                    $programaciones[] = $row;
                }
            }
            
            echo json_encode(['success' => true, 'data' => $programaciones]);
            break;
            
        case 'obtener':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id == 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                break;
            }
            
            $sql = "SELECT * FROM programaciones WHERE id = $id";
            $result = mysql_query($sql);
            
            if ($result && mysql_num_rows($result) > 0) {
                $programacion = mysql_fetch_assoc($result);
                echo json_encode(['success' => true, 'data' => $programacion]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Programación no encontrada']);
            }
            break;
            
        case 'guardar':
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (!$datos) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                break;
            }
            
            $idMateria = intval($datos['idMateria']);
            $idGrupo = isset($datos['idGrupo']) ? intval($datos['idGrupo']) : 0;
            $curso = mysql_real_escape_string($datos['curso']);
            $anyo = isset($datos['anyo']) ? mysql_real_escape_string($datos['anyo']) : '';
            $profesor = isset($datos['profesor']) ? mysql_real_escape_string($datos['profesor']) : '';
            $objetivos = isset($datos['objetivos']) ? mysql_real_escape_string($datos['objetivos']) : '';
            $metodologia = isset($datos['metodologia']) ? mysql_real_escape_string($datos['metodologia']) : '';
            $evaluacion = isset($datos['evaluacion']) ? mysql_real_escape_string($datos['evaluacion']) : '';
            $atencion_diversidad = isset($datos['atencion_diversidad']) ? mysql_real_escape_string($datos['atencion_diversidad']) : '';
            $materiales = isset($datos['materiales']) ? mysql_real_escape_string($datos['materiales']) : '';
            $bibliografia = isset($datos['bibliografia']) ? mysql_real_escape_string($datos['bibliografia']) : '';
            
            if (isset($datos['id']) && $datos['id'] > 0) {
                // Actualizar
                $id = intval($datos['id']);
                $sql = "UPDATE programaciones SET 
                        idMateria = $idMateria,
                        idGrupo = $idGrupo,
                        curso = '$curso',
                        anyo = '$anyo',
                        profesor = '$profesor',
                        objetivos = '$objetivos',
                        metodologia = '$metodologia',
                        evaluacion = '$evaluacion',
                        atencion_diversidad = '$atencion_diversidad',
                        materiales = '$materiales',
                        bibliografia = '$bibliografia'
                        WHERE id = $id";
                
                $result = mysql_query($sql);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Programación actualizada correctamente', 'id' => $id]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysql_error()]);
                }
            } else {
                // Crear nueva
                $sql = "INSERT INTO programaciones (idMateria, idGrupo, curso, anyo, profesor, objetivos, metodologia, evaluacion, atencion_diversidad, materiales, bibliografia) 
                        VALUES ($idMateria, $idGrupo, '$curso', '$anyo', '$profesor', '$objetivos', '$metodologia', '$evaluacion', '$atencion_diversidad', '$materiales', '$bibliografia')";
                
                $result = mysql_query($sql);
                
                if ($result) {
                    $id = mysql_insert_id();
                    echo json_encode(['success' => true, 'message' => 'Programación creada correctamente', 'id' => $id]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear: ' . mysql_error()]);
                }
            }
            break;
            
        case 'eliminar':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id == 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                break;
            }
            
            $sql = "DELETE FROM programaciones WHERE id = $id";
            $result = mysql_query($sql);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Programación eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysql_error()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
