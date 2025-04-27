<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Cargar WordPress
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php'); // Conexión a PostgreSQL

// Verificar autenticación del usuario en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida, se esperaba JSON']);
    exit;
}

// Leer el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['nombre'], $data['fecha'], $data['ubicacion'], $data['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$nombre = $data['nombre'];
$fecha = $data['fecha'];
$ubicacion = $data['ubicacion'];
$estado = $data['estado'];

// Validar que el estado sea uno de los permitidos
$estados_permitidos = ['PLANEACION', 'REGISTRO', 'EN_CURSO', 'FINALIZADO', 'CANCELADO'];
if (!in_array($estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado no válido']);
    exit;
}

$query = "INSERT INTO congresos (nombre, fecha, ubicacion, estado) VALUES ($1, $2, $3, $4) RETURNING id";
$result = pg_query_params($conexion, $query, [$nombre, $fecha, $ubicacion, $estado]);

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode(['success' => true, 'id' => $row['id'], 'message' => 'Congreso creado exitosamente.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el congreso.']);
}

pg_close($conexion);
exit;
