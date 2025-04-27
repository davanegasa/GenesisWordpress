<?php
    require_once(__DIR__ . '/../../../../../wp-load.php');
    require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
    
    // Verificar autenticación
    if (!is_user_logged_in()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
    
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método no permitido
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    // Leer datos
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['nombre'], $data['nivel_id'], $data['descripcion'])) {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        exit;
    }
    
    $nombre = pg_escape_string($conexion, $data['nombre']);
    $nivel_id = intval($data['nivel_id']);
    $descripcion = pg_escape_string($conexion, $data['descripcion']);
    
    // Insertar curso
    $query = "INSERT INTO cursos (nombre, nivel_id, descripcion) VALUES ('$nombre', $nivel_id, '$descripcion')";
    $result = pg_query($conexion, $query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Curso creado exitosamente']);
    } else {
        http_response_code(500); // Error del servidor
        echo json_encode(['error' => 'Error al crear el curso']);
    }
    
    pg_close($conexion);
?>