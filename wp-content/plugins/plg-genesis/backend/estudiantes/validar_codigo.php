<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

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

if (!isset($_POST['codigo_estudiante']) || empty($_POST['codigo_estudiante'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Código vacío']);
    exit;
}

$codigoIngresado = $_POST['codigo_estudiante'];
$query = "SELECT COUNT(*) AS count FROM estudiantes WHERE id_estudiante = $1";
$params = [$codigoIngresado];

$result = pg_query_params($conexion, $query, $params);

if (!$result) {
    require_once __DIR__ . '/../utils/logger.php';
    genesis_log('Error en la consulta de validar_codigo: ' . pg_last_error($conexion), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
    exit;
}

$row = pg_fetch_assoc($result);

if ($row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'El código de estudiante ya existe.']);
} else {
    echo json_encode(['success' => true, 'message' => 'Código válido.']);
}

pg_close($conexion);
?>
