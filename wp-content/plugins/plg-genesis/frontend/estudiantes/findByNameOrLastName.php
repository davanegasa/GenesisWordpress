<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
if (!$conexion) {
    error_log("Error en la conexión: " . pg_last_error(), 3, __DIR__ . "/error_log.txt");
    die("Error en la conexión a la base de datos.");
}

// Obtener los datos de búsqueda por POST
$nombre = pg_escape_string($conexion, $_POST['nombre'] ?? '');
$apellido = pg_escape_string($conexion, $_POST['apellido'] ?? '');
$contacto = pg_escape_string($conexion, $_POST['id_contacto'] ?? '');

// Construir la consulta SQL para buscar estudiantes con el ID de contacto
$query = "SELECT e.* FROM estudiantes e
          LEFT JOIN contactos c ON e.id_contacto = c.id
          WHERE (e.nombre1 ILIKE '%$nombre%' OR e.nombre2 ILIKE '%$nombre%')
          AND (e.apellido1 ILIKE '%$apellido%' OR e.apellido2 ILIKE '%$apellido%')";

// Agregar filtro de id_contacto solo si está presente
if (!empty($contacto)) {
    $query .= " AND c.code = '$contacto'";
}

// Ejecutar la consulta
$resultado = pg_query($conexion, $query);

// Crear un array para almacenar los resultados
$estudiantes = array();

// Verificar si se encontraron resultados
if ($resultado && pg_num_rows($resultado) > 0) {
    // Iterar sobre los resultados y añadirlos al array de estudiantes
    while ($row = pg_fetch_assoc($resultado)) {
        $estudiantes[] = $row;
    }
}

// Cerrar la conexión a la base de datos
pg_close($conexion);

// Convertir el array de estudiantes a formato JSON y enviarlo como respuesta
header('Content-Type: application/json');
echo json_encode($estudiantes);
?>