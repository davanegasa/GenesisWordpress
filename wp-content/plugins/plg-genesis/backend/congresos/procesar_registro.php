<?php
require_once(__DIR__ . '/../../backend/config.php'); // Cargar clave API
require_once(__DIR__ . '/../../backend/db_public.php'); // Conexión segura

// Configurar encabezados para JSON
header('Content-Type: application/json');

// Validar que la solicitud sea POST con JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida, se esperaba JSON']);
    exit;
}

// Leer el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

// Validar que la solicitud incluya la clave API
if (!isset($data['api_key']) || $data['api_key'] !== API_SECRET) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// Validar datos obligatorios
if (!isset($data['numero_boleta'], $data['codigo_verificacion'], $data['nombre'], $data['identificacion'], $data['telefono'], $data['email'], $data['congregacion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];
$nombre = $data['nombre'];
$identificacion = $data['identificacion'];
$telefono = $data['telefono'];
$email = $data['email'];
$congregacion = $data['congregacion'];
$id_congreso = $data['id_congreso'];

// Verificar si la boleta es válida y no usada
$query = "SELECT id, estado FROM boletas_congresos WHERE numero_boleta = $1 AND codigo_verificacion = $2";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['error' => 'Boleta inválida']);
    exit;
}

$boleta = pg_fetch_assoc($result);
if ($boleta['estado'] === 'usado') {
    echo json_encode(['error' => 'Esta boleta ya ha sido usada']);
    exit;
}

// Verificar si ya existe en estudiantes
$query = "SELECT id FROM estudiantes WHERE doc_identidad = $1";
$result = pg_query_params($conexion, $query, [$identificacion]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $id_estudiante = $row['id'];

    // Asociar la boleta con el estudiante y marcarla como usada
    $query = "UPDATE boletas_congresos SET id_estudiante = $1, estado = 'usado' WHERE id = $2";
    pg_query_params($conexion, $query, [$id_estudiante, $boleta['id']]);

    echo json_encode(['success' => true, 'message' => 'Registro actualizado para estudiante existente']);
    exit;
}

// Verificar si ya existe el asistente externo
$query = "SELECT id FROM asistentes_externos WHERE identificacion = $1";
$result = pg_query_params($conexion, $query, [$identificacion]);

if ($result && pg_num_rows($result) > 0) {
    // Actualizar los datos del asistente externo
    //$query = "UPDATE asistentes_externos SET nombre = $1, telefono = $2, email = $3, congregacion = $4 WHERE identificacion = $5 RETURNING id";
    //$result = pg_query_params($conexion, $query, [$nombre, $telefono, $email, $congregacion, $identificacion]);
} else {
    // Insertar nuevo asistente externo
    $query = "INSERT INTO asistentes_externos (nombre, id_contacto, identificacion, telefono, email, congregacion) VALUES ($1, '108',$2, $3, $4, $5) RETURNING id";
    $result = pg_query_params($conexion, $query, [$nombre, $identificacion, $telefono, $email, $congregacion]);
}

if ($result) {
    $row = pg_fetch_assoc($result);
    $id_asistente = $row['id'];

    // Asociar la boleta con el asistente y marcarla como usada
    $query = "UPDATE boletas_congresos SET id_asistente = $1, estado = 'usado' WHERE id = $2";
    pg_query_params($conexion, $query, [$id_asistente, $boleta['id']]);

    echo json_encode(['success' => true, 'message' => 'Registro exitoso']);
} else {
    echo json_encode(['error' => 'Error al registrar el asistente']);
}

pg_close($conexion);
exit;
