<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si el usuario está autenticado
if (!is_user_logged_in()) {
    wp_send_json_error('No autorizado');
    exit;
}

// Verificar si se recibieron los datos necesarios
if (!isset($_POST['id'])) {
    wp_send_json_error('Falta el ID del registro');
    exit;
}

// Verificar que el usuario de WordPress sea laura.vanegas
$current_user = wp_get_current_user();
if ($current_user->user_login !== 'laura.vanegas') {
    wp_send_json_error('No tienes permisos para realizar esta acción');
    exit;
}

$id = intval($_POST['id']);

// Eliminar el registro específico de la tabla estudiantes_cursos
$query = "DELETE FROM estudiantes_cursos WHERE id = $id";

$result = pg_query($conexion, $query);

if ($result) {
    wp_send_json_success('Curso eliminado correctamente');
} else {
    wp_send_json_error('Error al eliminar el curso: ' . pg_last_error($conexion));
}

// Cerrar la conexión
pg_close($conexion);
?> 