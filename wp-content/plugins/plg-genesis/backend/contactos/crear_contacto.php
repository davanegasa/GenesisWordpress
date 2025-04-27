<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'] ?? null;
$iglesia = $data['iglesia'] ?? null;
$email = $data['email'] ?? null;
$celular = $data['celular'] ?? null;
$direccion = $data['direccion'] ?? null;
$ciudad = $data['ciudad'] ?? null;
$code = $data['code'] ?? null;

if (!$nombre || !$celular) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios.']);
    exit;
}

$query = "
    INSERT INTO contactos (nombre, iglesia, email, celular, direccion, ciudad, code)
    VALUES ($1, $2, $3, $4, $5, $6, $7)
    RETURNING id
";
$result = pg_query_params($conexion, $query, [$nombre, $iglesia, $email, $celular, $direccion, $ciudad, $code]);

if ($result && pg_affected_rows($result) > 0) {
    $contacto = pg_fetch_assoc($result);
    echo json_encode(['success' => true, 'id' => $contacto['id']]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el contacto.']);
}

pg_close($conexion);
?>