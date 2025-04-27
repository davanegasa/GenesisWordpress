<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json; charset=UTF-8');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Error en la estructura JSON recibida']);
    exit;
}

if (!isset($data['estudiante_id']) || !is_numeric($data['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de estudiante no válido']);
    exit;
}

if (!isset($data['observacion']) || empty(trim($data['observacion']))) {
    http_response_code(400);
    echo json_encode(['error' => 'La observación es obligatoria']);
    exit;
}

$estudiante_id = intval($data['estudiante_id']);
$observacion = trim($data['observacion']);
$usuario_id = get_current_user_id(); // Usuario autenticado en WordPress

if (!$usuario_id) {
    http_response_code(403);
    echo json_encode(['error' => 'No se pudo obtener el usuario autenticado']);
    exit;
}

$tipo = isset($data['tipo']) ? trim($data['tipo']) : 'General';

// Validar conexión a la base de datos
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la conexión a la base de datos']);
    exit;
}

// Query con placeholders para evitar inyección SQL
$query = "
    INSERT INTO observaciones_estudiantes (estudiante_id, observacion, fecha, usuario_id, tipo)
    VALUES ($1, $2, NOW(), $3, $4)
    RETURNING id;
";

$params = [$estudiante_id, $observacion, $usuario_id, $tipo];
$result = pg_query_params($conexion, $query, $params);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la observación', 'db_error' => pg_last_error($conexion)]);
    exit;
}

// Validar si se insertó correctamente
$inserted_id = pg_fetch_result($result, 0, 0);
if (!$inserted_id) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo obtener el ID de la observación guardada']);
    exit;
}

$response = [
    'success' => true,
    'message' => 'Observación registrada con éxito',
    'observacion_id' => intval($inserted_id)
];

// Validar la respuesta JSON antes de enviarla
$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);
if ($jsonResponse === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al generar la respuesta JSON', 'json_error' => json_last_error_msg()]);
    exit;
}

echo $jsonResponse;

pg_close($conexion);
?>