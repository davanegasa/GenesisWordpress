<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_congreso = isset($data['id_congreso']) ? intval($data['id_congreso']) : 0;
if ($id_congreso <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de congreso inválido']);
    exit;
}

// Total inscritos (solo estado 'usado')
$q1 = pg_query_params($conexion, 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2', [$id_congreso, 'usado']);
$total_inscritos = pg_fetch_result($q1, 0, 0);
// Total llegadas (solo estado 'usado')
$q2 = pg_query_params($conexion, 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND fecha_llegada IS NOT NULL', [$id_congreso, 'usado']);
$total_llegadas = pg_fetch_result($q2, 0, 0);
// Total almuerzos (solo estado 'usado')
$q3 = pg_query_params($conexion, 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND fecha_almuerzo IS NOT NULL', [$id_congreso, 'usado']);
$total_almuerzos = pg_fetch_result($q3, 0, 0);

echo json_encode([
    'success' => true,
    'total_inscritos' => (int)$total_inscritos,
    'total_llegadas' => (int)$total_llegadas,
    'total_almuerzos' => (int)$total_almuerzos
]);
pg_close($conexion);
exit; 