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

// Verificar si el estudiante existe
$query_check = "SELECT id FROM estudiantes WHERE id = $1";
$result_check = pg_query_params($conexion, $query_check, [$estudiante_id]);

if (!$result_check) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error al verificar estudiante',
        'db_error' => pg_last_error($conexion)
    ]);
    exit;
}

if (pg_num_rows($result_check) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Estudiante no encontrado']);
    exit;
}

// Obtener el último curso del estudiante
$query = "
    SELECT 
        c.nombre,
        c.descripcion,
        ec.porcentaje,
        ec.fecha
    FROM estudiantes_cursos ec
    JOIN cursos c ON c.id = ec.curso_id
    WHERE ec.estudiante_id = $1
    ORDER BY ec.fecha DESC
    LIMIT 1
";

$result = pg_query_params($conexion, $query, [$estudiante_id]);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error al consultar el último curso',
        'db_error' => pg_last_error($conexion)
    ]);
    exit;
}

$curso = pg_fetch_assoc($result);

echo json_encode([
    'success' => true,
    'curso' => $curso
]);

pg_close($conexion);
?> 