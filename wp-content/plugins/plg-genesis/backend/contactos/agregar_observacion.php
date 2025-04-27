<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar que sea una petici¨®n POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'M¨¦todo no permitido']);
    exit;
}

// Obtener el nombre del usuario actual
$current_user = wp_get_current_user();
$usuario_nombre = $current_user->display_name ?: $current_user->user_login;

// Obtener y validar los datos
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['contacto_id']) || !isset($data['observacion']) || !isset($data['tipo'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$contactoId = intval($data['contacto_id']);
$observacion = pg_escape_string($data['observacion']);
$tipo = pg_escape_string($data['tipo']);

// Insertar la observaci¨®n
$query = "
    INSERT INTO observaciones_contactos (contacto_id, observacion, tipo, usuario_nombre)
    VALUES ($1, $2, $3, $4)
    RETURNING id
";

$result = pg_query_params($conexion, $query, [$contactoId, $observacion, $tipo, $usuario_nombre]);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Error al insertar la observaci¨®n']);
    exit;
}

$row = pg_fetch_assoc($result);
echo json_encode(['success' => true, 'id' => $row['id']]);

pg_close($conexion);