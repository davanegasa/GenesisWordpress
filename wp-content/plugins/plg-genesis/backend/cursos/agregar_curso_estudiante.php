<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../db.php');

header('Content-Type: application/json');

// Verificar si el método es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener los datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar que los campos requeridos existan
if (!isset($data['estudiante_id']) || !isset($data['curso_id'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Faltan parámetros obligatorios (estudiante_id y curso_id)']);
    exit;
}

$estudiante_id = intval($data['estudiante_id']);
$curso_id = intval($data['curso_id']);

// Verificar si el estudiante existe
$query_check_estudiante = "SELECT id FROM estudiantes WHERE id = $1";
$result_estudiante = pg_query_params($conexion, $query_check_estudiante, [$estudiante_id]);

if (!$result_estudiante || pg_num_rows($result_estudiante) === 0) {
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'El estudiante no existe']);
    exit;
}

// Verificar si el curso existe
$query_check_curso = "SELECT id FROM cursos WHERE id = $1";
$result_curso = pg_query_params($conexion, $query_check_curso, [$curso_id]);

if (!$result_curso || pg_num_rows($result_curso) === 0) {
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'El curso no existe']);
    exit;
}

// Verificar si la relación ya existe
$query_check_relacion = "SELECT id FROM estudiantes_cursos WHERE estudiante_id = $1 AND curso_id = $2";
$result_relacion = pg_query_params($conexion, $query_check_relacion, [$estudiante_id, $curso_id]);

if (pg_num_rows($result_relacion) > 0) {
    http_response_code(409); // Conflicto
    echo json_encode(['error' => 'El curso ya está asignado al estudiante']);
    exit;
}

// Insertar la relación
$query_insert = "INSERT INTO estudiantes_cursos (estudiante_id, curso_id, fecha) VALUES ($1, $2, NOW())";
$result_insert = pg_query_params($conexion, $query_insert, [$estudiante_id, $curso_id]);

if ($result_insert) {
    echo json_encode(['success' => true, 'message' => 'Curso asignado exitosamente']);
} else {
    http_response_code(500); // Error del servidor
    echo json_encode(['error' => 'Error al asignar el curso']);
}

pg_close($conexion);
?>