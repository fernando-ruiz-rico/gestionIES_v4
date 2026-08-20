<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'cargar_apartados' && isset($_GET['idMateria'])) {
                $idMateria = intval($_GET['idMateria']);

                // Determinar si es materia de ciclos (FP) o ESO/BACH
                $sql = "SELECT ciclos.id AS id 
                        FROM ciclos, cursos, cursos_ciclos, materias 
                        WHERE ciclos.id = cursos_ciclos.idCiclo 
                        AND cursos.id = cursos_ciclos.idCurso 
                        AND materias.idCurso = cursos.id 
                        AND materias.id = $idMateria";
                $result = mysqli_query($db, $sql);
                $categoria = 'ESO/BACH';
                if ($fila = mysqli_fetch_assoc($result)) {
                    $categoria = 'FP';
                }
                mysqli_free_result($result);

                // Cargar apartados según categoría
                $sql = "SELECT * FROM apartados_programaciones 
                        WHERE categoria='TODOS' OR categoria='$categoria' 
                        ORDER BY orden";
                $result = mysqli_query($db, $sql);
                
                $apartados = [];
                $cont = 0;
                $cont2 = 0;
                
                while ($fila = mysqli_fetch_assoc($result)) {
                    $id = $fila['id'];
                    $titulo = $fila['titulo'];
                    $subapartado = $fila['subapartado'];
                    $tipo = $fila['tipo'];
                    
                    if (!$subapartado) {
                        $cont++;
                        $cont2 = 0;
                        $apartados[] = [
                            'id' => $id,
                            'tipo' => $tipo,
                            'nombre' => "$cont. $titulo"
                        ];
                    } else {
                        $cont2++;
                        $apartados[] = [
                            'id' => $id,
                            'tipo' => $tipo,
                            'nombre' => "$cont.$cont2. $titulo"
                        ];
                    }
                }
                mysqli_free_result($result);
                
                echo json_encode(['success' => true, 'data' => $apartados]);
                
            } elseif ($action === 'cargar_contenido' && isset($_GET['idApartado']) && isset($_GET['idMateria'])) {
                $idApartado = intval($_GET['idApartado']);
                $idMateria = intval($_GET['idMateria']);
                
                $sql = "SELECT texto FROM contenidos_programaciones 
                        WHERE idApartado = $idApartado AND idMateria = $idMateria";
                $result = mysqli_query($db, $sql);
                
                $texto = "";
                if ($fila = mysqli_fetch_assoc($result)) {
                    $texto = $fila['texto'];
                }
                mysqli_free_result($result);
                
                echo json_encode(['success' => true, 'data' => ['texto' => $texto]]);
                
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            if ($action === 'guardar_contenido') {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                
                if (!isset($data['idApartado']) || !isset($data['idMateria'])) {
                    throw new Exception('Debe especificar idApartado e idMateria');
                }
                
                $idApartado = intval($data['idApartado']);
                $idMateria = intval($data['idMateria']);
                $texto = mysqli_real_escape_string($db, $data['texto']);
                
                // Verificar si ya existe
                $sql = "SELECT * FROM contenidos_programaciones 
                        WHERE idApartado = $idApartado AND idMateria = $idMateria";
                $result = mysqli_query($db, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    // Actualizar
                    $sql = "UPDATE contenidos_programaciones 
                            SET texto = '$texto' 
                            WHERE idApartado = $idApartado AND idMateria = $idMateria";
                    $result = mysqli_query($db, $sql);
                    if (!$result) {
                        throw new Exception(mysqli_error($db));
                    }
                    $afectados = mysqli_affected_rows($db);
                } else {
                    // Insertar
                    $sql = "INSERT INTO contenidos_programaciones (idApartado, idMateria, texto) 
                            VALUES ($idApartado, $idMateria, '$texto')";
                    $result = mysqli_query($db, $sql);
                    if (!$result) {
                        throw new Exception(mysqli_error($db));
                    }
                    $afectados = mysqli_affected_rows($db);
                }
                
                if ($afectados > 0) {
                    echo json_encode(['success' => true, 'message' => 'Contenido guardado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'No se realizaron cambios']);
                }
                
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    closeDBConnection($db);
}
?>
