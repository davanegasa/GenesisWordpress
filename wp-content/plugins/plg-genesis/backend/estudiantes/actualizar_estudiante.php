<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener los datos enviados desde el cliente
$data = json_decode(file_get_contents('php://input'), true);

// Validar que el id_estudiante sea enviado
if (!isset($data['id_estudiante'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'El campo id_estudiante es obligatorio']);
    exit;
}

// Escapar y validar el id_estudiante
$id_estudiante = intval($data['id_estudiante']);

// Obtener los datos actuales del estudiante
$query_select = "SELECT * FROM estudiantes WHERE id = $id_estudiante";
$result_select = pg_query($conexion, $query_select);

if (!$result_select || pg_num_rows($result_select) === 0) {
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'Estudiante no encontrado']);
    exit;
}

$estudiante_actual = pg_fetch_assoc($result_select);

// Construir la consulta de actualización solo con los campos enviados
$campos_actualizar = [];
if (isset($data['nombre1'])) $campos_actualizar[] = "nombre1 = '" . pg_escape_string($conexion, $data['nombre1']) . "'";
if (isset($data['nombre2'])) $campos_actualizar[] = "nombre2 = '" . pg_escape_string($conexion, $data['nombre2']) . "'";
if (isset($data['apellido1'])) $campos_actualizar[] = "apellido1 = '" . pg_escape_string($conexion, $data['apellido1']) . "'";
if (isset($data['apellido2'])) $campos_actualizar[] = "apellido2 = '" . pg_escape_string($conexion, $data['apellido2']) . "'";
if (isset($data['email'])) $campos_actualizar[] = "email = '" . pg_escape_string($conexion, $data['email']) . "'";
if (isset($data['celular'])) $campos_actualizar[] = "celular = '" . pg_escape_string($conexion, $data['celular']) . "'";
if (isset($data['ciudad'])) $campos_actualizar[] = "ciudad = '" . pg_escape_string($conexion, $data['ciudad']) . "'";
if (isset($data['doc_identidad'])) $campos_actualizar[] = "doc_identidad = '" . pg_escape_string($conexion, $data['doc_identidad']) . "'";
if (isset($data['id_contacto'])) $campos_actualizar[] = "id_contacto = " . intval($data['id_contacto']);
if (isset($data['iglesia'])) $campos_actualizar[] = "iglesia = '" . pg_escape_string($conexion, $data['iglesia']) . "'";

if (!empty($campos_actualizar)) {
    $query_update = "UPDATE estudiantes SET " . implode(', ', $campos_actualizar) . " WHERE id = $id_estudiante";
    $result_update = pg_query($conexion, $query_update);

    if (!$result_update) {
        http_response_code(500); // Error del servidor
        echo json_encode(['error' => 'Error al actualizar el estudiante']);
        exit;
    }
}

// Obtener los datos actualizados del estudiante
$result_select_updated = pg_query($conexion, $query_select);

if ($result_select_updated && pg_num_rows($result_select_updated) > 0) {
    $estudiante_actualizado = pg_fetch_assoc($result_select_updated);
    echo json_encode(['success' => true, 'estudiante' => $estudiante_actualizado]);
} else {
    http_response_code(500); // Error del servidor
    echo json_encode(['error' => 'Error al obtener los datos actualizados del estudiante']);
}

pg_close($conexion);
?>