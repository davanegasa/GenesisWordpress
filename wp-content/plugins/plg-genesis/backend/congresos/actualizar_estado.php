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

if (!$data || !isset($data['id_congreso'], $data['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_congreso = $data['id_congreso'];
$estado = $data['estado'];

// Validar que el estado sea uno de los permitidos
$estados_permitidos = ['PLANEACION', 'REGISTRO', 'EN_CURSO', 'FINALIZADO', 'CANCELADO'];
if (!in_array($estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado no válido']);
    exit;
}

// Actualizar el estado del congreso
$query = "UPDATE congresos SET estado = $1 WHERE id = $2 RETURNING id";
$result = pg_query_params($conexion, $query, [$estado, $id_congreso]);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode([
        'success' => true, 
        'message' => 'Estado del congreso actualizado exitosamente.',
        'estado' => $estado
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el estado del congreso.']);
}

pg_close($conexion);
exit; 