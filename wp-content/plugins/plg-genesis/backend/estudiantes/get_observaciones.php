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
$limite = isset($_GET['limite']) && is_numeric($_GET['limite']) ? intval($_GET['limite']) : null;

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

$query = "
    SELECT 
        id,
        observacion,
        fecha,
        tipo,
        usuario_id
    FROM observaciones_estudiantes
    WHERE estudiante_id = $1 
    ORDER BY fecha DESC
";

if ($limite !== null) {
    $query .= " LIMIT $2";
    $params = [$estudiante_id, $limite];
} else {
$params = [$estudiante_id];
}

$result = pg_query_params($conexion, $query, $params);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error al consultar las observaciones',
        'db_error' => pg_last_error($conexion),
        'query' => $query,
        'params' => $params
    ]);
    exit;
}

$observaciones = pg_fetch_all($result);

// Si hay observaciones, obtener los nombres de usuario de WordPress
if ($observaciones) {
    foreach ($observaciones as &$obs) {
        if (!empty($obs['usuario_id'])) {
            $user_info = get_userdata($obs['usuario_id']);
            $obs['usuario_nombre'] = $user_info ? $user_info->display_name : 'Usuario ' . $obs['usuario_id'];
        } else {
            $obs['usuario_nombre'] = 'Sistema';
        }
    }
    unset($obs); // Romper la referencia
}

echo json_encode([
    'success' => true, 
    'observaciones' => $observaciones ?? [],
    'estudiante_id' => $estudiante_id
]);

pg_close($conexion);
?>