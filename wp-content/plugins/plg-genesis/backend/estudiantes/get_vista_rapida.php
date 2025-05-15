<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de estudiante no proporcionado o inválido']);
    exit;
}

$estudiante_id = intval($_GET['id']);

// Verificar la conexión a la base de datos
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // 1. Obtener información básica del estudiante
    $query_estudiante = "
        SELECT 
            e.*,
            c.nombre as contacto_nombre,
            c.iglesia as contacto_iglesia,
            c.code as contacto_code
        FROM estudiantes e
        LEFT JOIN contactos c ON e.id_contacto = c.id
        WHERE e.id = $1
    ";
    
    $result_estudiante = pg_query_params($conexion, $query_estudiante, [$estudiante_id]);
    if (!$result_estudiante) throw new Exception("Error al obtener datos del estudiante: " . pg_last_error($conexion));
    
    $estudiante = pg_fetch_assoc($result_estudiante);
    if (!$estudiante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Estudiante no encontrado']);
        exit;
    }

    // 2. Obtener último curso
    $query_curso = "
        SELECT 
            c.nombre,
            c.descripcion,
            n.nombre as nivel,
            ec.porcentaje,
            ec.fecha
        FROM estudiantes_cursos ec
        JOIN cursos c ON c.id = ec.curso_id
        JOIN niveles n ON n.id = c.nivel_id
        WHERE ec.estudiante_id = $1
        ORDER BY ec.fecha DESC
        LIMIT 1
    ";
    
    $result_curso = pg_query_params($conexion, $query_curso, [$estudiante_id]);
    if (!$result_curso) throw new Exception("Error al obtener último curso: " . pg_last_error($conexion));
    
    $ultimo_curso = pg_fetch_assoc($result_curso);

    // 3. Obtener última observación
    $query_observacion = "
        SELECT 
            o.observacion,
            o.fecha,
            o.tipo,
            o.usuario_id
        FROM observaciones_estudiantes o
        WHERE o.estudiante_id = $1
        ORDER BY o.fecha DESC
        LIMIT 1
    ";
    
    $result_observacion = pg_query_params($conexion, $query_observacion, [$estudiante_id]);
    if (!$result_observacion) throw new Exception("Error al obtener última observación: " . pg_last_error($conexion));
    
    $ultima_observacion = pg_fetch_assoc($result_observacion);
    
    // Si hay una observación, obtener el nombre del usuario
    if ($ultima_observacion && !empty($ultima_observacion['usuario_id'])) {
        $user_info = get_userdata($ultima_observacion['usuario_id']);
        $ultima_observacion['usuario_nombre'] = $user_info ? $user_info->display_name : 'Usuario ' . $ultima_observacion['usuario_id'];
    }

    // 4. Obtener estadísticas
    $query_stats = "
        SELECT 
            COUNT(*) as total_cursos,
            AVG(porcentaje) as promedio_porcentaje,
            MAX(fecha) as ultima_actividad
        FROM estudiantes_cursos
        WHERE estudiante_id = $1
    ";
    
    $result_stats = pg_query_params($conexion, $query_stats, [$estudiante_id]);
    if (!$result_stats) throw new Exception("Error al obtener estadísticas: " . pg_last_error($conexion));
    
    $estadisticas = pg_fetch_assoc($result_stats);

    // Preparar la respuesta
    $response = [
        'success' => true,
        'estudiante' => [
            'id' => $estudiante['id'],
            'codigo' => $estudiante['id_estudiante'],
            'nombre_completo' => trim($estudiante['nombre1'] . ' ' . 
                                    ($estudiante['nombre2'] ? $estudiante['nombre2'] . ' ' : '') . 
                                    $estudiante['apellido1'] . ' ' . 
                                    ($estudiante['apellido2'] ? $estudiante['apellido2'] : '')),
            'documento' => $estudiante['doc_identidad'],
            'email' => $estudiante['email'],
            'celular' => $estudiante['celular'],
            'ciudad' => $estudiante['ciudad'],
            'iglesia' => $estudiante['iglesia'],
            'contacto' => [
                'nombre' => $estudiante['contacto_nombre'],
                'iglesia' => $estudiante['contacto_iglesia'],
                'codigo' => $estudiante['contacto_code']
            ]
        ],
        'ultimo_curso' => $ultimo_curso,
        'ultima_observacion' => $ultima_observacion,
        'estadisticas' => [
            'total_cursos' => intval($estadisticas['total_cursos']),
            'promedio_porcentaje' => round(floatval($estadisticas['promedio_porcentaje']), 1),
            'ultima_actividad' => $estadisticas['ultima_actividad']
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar la solicitud',
        'message' => $e->getMessage()
    ]);
}

pg_close($conexion);
?> 