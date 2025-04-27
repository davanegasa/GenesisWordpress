<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Leer datos del request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['nombre'], $data['descripcion'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

$id = intval($data['id']);
$nombre = trim($data['nombre']);
$descripcion = trim($data['descripcion']);

// Verificar que los valores no estén vacíos
if (empty($nombre) || empty($descripcion)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// Conectar a la base de datos
if (!$conexion) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Usar consulta preparada para evitar SQL Injection
$query = "UPDATE cursos SET nombre = $1, descripcion = $2 WHERE id = $3";
$result = pg_query_params($conexion, $query, [$nombre, $descripcion, $id]);

// Verificar resultado
if ($result) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Curso actualizado exitosamente']);
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al actualizar el curso', 'detalle' => pg_last_error($conexion)]);
}

// Cerrar conexión
pg_close($conexion);
?>