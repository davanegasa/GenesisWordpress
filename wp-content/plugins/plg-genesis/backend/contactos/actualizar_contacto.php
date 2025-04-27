<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener el cuerpo de la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar que se envíe el ID del contacto
if (!isset($data['id']) || empty($data['id'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'ID del contacto es requerido']);
    exit;
}

// ID del contacto
$id = intval($data['id']);

// Obtener los datos actuales del contacto
$query = "SELECT * FROM contactos WHERE id = $1";
$result = pg_query_params($conexion, $query, [$id]);

if (!$result || pg_num_rows($result) === 0) {
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'Contacto no encontrado']);
    exit;
}

$currentData = pg_fetch_assoc($result);

// Actualizar solo los campos enviados
$nombre = isset($data['nombre']) ? $data['nombre'] : $currentData['nombre'];
$iglesia = isset($data['iglesia']) ? $data['iglesia'] : $currentData['iglesia'];
$email = isset($data['email']) ? $data['email'] : $currentData['email'];
$celular = isset($data['celular']) ? $data['celular'] : $currentData['celular'];
$direccion = isset($data['direccion']) ? $data['direccion'] : $currentData['direccion'];
$ciudad = isset($data['ciudad']) ? $data['ciudad'] : $currentData['ciudad'];
$code = isset($data['code']) ? $data['code'] : $currentData['code'];

// Actualizar los datos en la base de datos
$updateQuery = "
    UPDATE contactos
    SET nombre = $1, iglesia = $2, email = $3, celular = $4, direccion = $5, ciudad = $6, code = $7
    WHERE id = $8
";
$updateResult = pg_query_params($conexion, $updateQuery, [
    $nombre, $iglesia, $email, $celular, $direccion, $ciudad, $code, $id
]);

if ($updateResult) {
    echo json_encode(['success' => true, 'message' => 'Contacto actualizado exitosamente']);
} else {
    http_response_code(500); // Error interno del servidor
    echo json_encode(['error' => 'Error al actualizar el contacto']);
}

pg_close($conexion);
?>
