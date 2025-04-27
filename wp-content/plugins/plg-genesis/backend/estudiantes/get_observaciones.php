<?php
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

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de estudiante inválido']);
    exit;
}

$estudiante_id = intval($_GET['estudiante_id']);

$query = "SELECT observacion, fecha FROM observaciones_estudiantes WHERE estudiante_id = $1 ORDER BY fecha DESC";
$params = [$estudiante_id];

$result = pg_query_params($conexion, $query, $params);
$observaciones = pg_fetch_all($result);

echo json_encode(['success' => true, 'observaciones' => $observaciones ?? []]);
pg_close($conexion);
?>