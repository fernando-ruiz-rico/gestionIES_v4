<?php
header('Content-Type: application/json');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
                
                if ($idMateria > 0) {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                            FROM programaciones p 
                            LEFT JOIN materias m ON p.idMateria = m.id 
                            LEFT JOIN grupos g ON p.idGrupo = g.id 
                            WHERE p.idMateria = ? 
                            ORDER BY p.curso DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idMateria]);
                } else {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                            FROM programaciones p 
                            LEFT JOIN materias m ON p.idMateria = m.id 
                            LEFT JOIN grupos g ON p.idGrupo = g.id 
                            ORDER BY p.curso DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $sql = "SELECT * FROM programaciones WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Programación no encontrada']);
                }
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $sql = "UPDATE programaciones SET 
                        idMateria = ?, 
                        idGrupo = ?, 
                        curso = ?, 
                        anyo = ?, 
                        profesor = ?, 
                        objetivos = ?, 
                        metodologia = ?, 
                        evaluacion = ?, 
                        atencion_diversidad = ?, 
                        materiales = ?, 
                        bibliografia = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: '',
                    $data['id']
                ]);
                $id = $data['id'];
            } else {
                // Crear
                $sql = "INSERT INTO programaciones (idMateria, idGrupo, curso, anyo, profesor, objetivos, metodologia, evaluacion, atencion_diversidad, materiales, bibliografia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: ''
                ]);
                $id = $pdo->lastInsertId();
            }
            
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $sql = "DELETE FROM programaciones WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('ID no proporcionado');
            }
            break;

        case 'POST':
            // Acción especial: importar programación desde otra materia
            if ($action === 'importar') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($data['idMateriaOrigen']) || !isset($data['idMateriaDestino'])) {
                    throw new Exception('Debe especificar materia origen y destino');
                }
                
                $idMateriaOrigen = intval($data['idMateriaOrigen']);
                $idMateriaDestino = intval($data['idMateriaDestino']);
                
                if ($idMateriaOrigen <= 0 || $idMateriaDestino <= 0) {
                    throw new Exception('IDs de materia inválidos');
                }
                
                // Iniciar transacción
                $pdo->beginTransaction();
                
                try {
                    // Borrar contenidos previos de programación destino
                    $stmt = $pdo->prepare("DELETE FROM contenidos_programaciones WHERE idMateria = ?");
                    $stmt->execute([$idMateriaDestino]);
                    
                    $stmt = $pdo->prepare("DELETE FROM competencias_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = ?)");
                    $stmt->execute([$idMateriaDestino]);
                    
                    $stmt = $pdo->prepare("DELETE FROM criterios_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = ?)");
                    $stmt->execute([$idMateriaDestino]);
                    
                    $stmt = $pdo->prepare("DELETE FROM temas WHERE idMateria = ?");
                    $stmt->execute([$idMateriaDestino]);
                    
                    // Insertar contenidos de la materia origen en la destino
                    $stmt = $pdo->prepare("INSERT INTO contenidos_programaciones(idMateria, idApartado, texto) SELECT ? AS idMateria, idApartado, texto FROM contenidos_programaciones WHERE idMateria = ?");
                    $stmt->execute([$idMateriaDestino, $idMateriaOrigen]);
                    
                    // Insertar temas
                    $stmt = $pdo->prepare("INSERT INTO temas(idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto) SELECT ? AS idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto FROM temas WHERE idMateria = ?");
                    $stmt->execute([$idMateriaDestino, $idMateriaOrigen]);
                    
                    // Insertar RA y CE asociados
                    $stmt = $pdo->prepare("SELECT criterios_temas.codigo as CE, temas.orden as tema, resultados_aprendizaje.orden as RA FROM criterios_temas, temas, resultados_aprendizaje WHERE criterios_temas.idRA = resultados_aprendizaje.id AND criterios_temas.idTema = temas.id AND temas.idMateria = ?");
                    $stmt->execute([$idMateriaOrigen]);
                    $criteriosOrigen = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($criteriosOrigen as $fila) {
                        $codigoCE = $fila['CE'];
                        $ordenRA = $fila['RA'];
                        $numTema = $fila['tema'];
                        
                        // Buscar el id del RA para la materia destino
                        $stmt2 = $pdo->prepare("SELECT id FROM resultados_aprendizaje WHERE idMateria = ? AND orden = ?");
                        $stmt2->execute([$idMateriaDestino, $ordenRA]);
                        $idRA = $stmt2->fetchColumn();
                        
                        // Buscar el id del tema para la materia destino
                        $stmt2 = $pdo->prepare("SELECT id FROM temas WHERE idMateria = ? AND orden = ?");
                        $stmt2->execute([$idMateriaDestino, $numTema]);
                        $idTema = $stmt2->fetchColumn();
                        
                        if ($idRA && $idTema) {
                            $stmt2 = $pdo->prepare("INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)");
                            $stmt2->execute([$idRA, $codigoCE, $idTema]);
                        }
                    }
                    
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Programación importada correctamente']);
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
            }
            
            // Si no es acción importar, procesar como creación/actualización normal
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $sql = "UPDATE programaciones SET 
                        idMateria = ?, 
                        idGrupo = ?, 
                        curso = ?, 
                        anyo = ?, 
                        profesor = ?, 
                        objetivos = ?, 
                        metodologia = ?, 
                        evaluacion = ?, 
                        atencion_diversidad = ?, 
                        materiales = ?, 
                        bibliografia = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: '',
                    $data['id']
                ]);
                $id = $data['id'];
            } else {
                // Crear
                $sql = "INSERT INTO programaciones (idMateria, idGrupo, curso, anyo, profesor, objetivos, metodologia, evaluacion, atencion_diversidad, materiales, bibliografia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: ''
                ]);
                $id = $pdo->lastInsertId();
            }
            
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
