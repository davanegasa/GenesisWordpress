<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validar entrada
if (!isset($data['id'], $data['curso_id'], $data['porcentaje'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$estudiante_id = intval($data['id']);
$curso_id = intval($data['curso_id']);
$porcentaje = intval($data['porcentaje']);

if ($porcentaje < 1 || $porcentaje > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El porcentaje debe estar entre 1 y 100']);
    exit;
}

// Verificar si el estudiante existe
$query_estudiante = "SELECT id FROM estudiantes WHERE id = $1";
$result_estudiante = pg_query_params($conexion, $query_estudiante, [$estudiante_id]);

if (!$result_estudiante || pg_num_rows($result_estudiante) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Estudiante no encontrado']);
    exit;
}

// Verificar si el curso existe
$query_curso = "SELECT id FROM cursos WHERE id = $1";
$result_curso = pg_query_params($conexion, $query_curso, [$curso_id]);

if (!$result_curso || pg_num_rows($result_curso) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Curso no encontrado']);
    exit;
}

// Verificar si ya existe la asignación
$query_existente = "SELECT id FROM estudiantes_cursos WHERE estudiante_id = $1 AND curso_id = $2";
$result_existente = pg_query_params($conexion, $query_existente, [$estudiante_id, $curso_id]);

$forzar = isset($data['forzar']) ? (bool)$data['forzar'] : false;

if ($result_existente && pg_num_rows($result_existente) > 0) {
    if (!$forzar) {
        // Obtener información del curso anterior
        $query_curso_anterior = "
            SELECT ec.porcentaje, ec.fecha, c.nombre as curso_nombre
            FROM estudiantes_cursos ec
            JOIN cursos c ON ec.curso_id = c.id
            WHERE ec.estudiante_id = $1 AND ec.curso_id = $2
            ORDER BY ec.fecha DESC
            LIMIT 1
        ";
        $result_curso_anterior = pg_query_params($conexion, $query_curso_anterior, [$estudiante_id, $curso_id]);
        
        $curso_anterior = null;
        if ($result_curso_anterior && pg_num_rows($result_curso_anterior) > 0) {
            $row = pg_fetch_assoc($result_curso_anterior);
            $curso_anterior = [
                'porcentaje' => $row['porcentaje'],
                'fecha' => $row['fecha'],
                'curso_nombre' => $row['curso_nombre']
            ];
        }
        
        http_response_code(409);
        echo json_encode([
            'success' => false, 
            'error' => 'El estudiante ya tiene asignado este curso',
            'suggestion' => 'Use el parámetro "forzar": true para permitir repetir el curso',
            'curso_anterior' => $curso_anterior
        ]);
        exit;
    } else {
        // Si se fuerza, permitir crear un nuevo registro (repetir curso)
        // Continuar con el INSERT normal
    }
}

// Insertar en la tabla estudiantes_cursos (solo si no existe)
$query = "
    INSERT INTO estudiantes_cursos (estudiante_id, curso_id, porcentaje, fecha)
    VALUES ($1, $2, $3, NOW())
    RETURNING id
";

$result = pg_query_params($conexion, $query, [$estudiante_id, $curso_id, $porcentaje]);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode(['success' => true, 'message' => 'Curso asignado exitosamente']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al asignar el curso']);
}

pg_close($conexion);
?>