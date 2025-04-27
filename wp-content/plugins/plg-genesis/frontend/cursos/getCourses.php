<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta seg¨²n tu estructura de directorios

// Verificar si el usuario no est¨¢ autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la p¨¢gina de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Consulta SQL para obtener la lista de cursos
$query = "SELECT id, nombre, descripcion FROM cursos";

// Ejecutar la consulta
$resultado = pg_query($conexion, $query);

// Crear un array para almacenar los cursos
$cursos = array();

// Verificar si se encontraron resultados
if ($resultado && pg_num_rows($resultado) > 0) {
    // Iterar sobre los resultados y aÃ±adirlos al array de cursos
    while ($row = pg_fetch_assoc($resultado)) {
        $cursos[] = $row;
    }
}

// Cerrar la conexiÃ³n a la base de datos
pg_close($conexion);

// Devolver la lista de cursos en formato JSON
header('Content-Type: application/json');
echo json_encode($cursos);
?>
