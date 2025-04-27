<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Definir la consulta SQL para obtener solo los registros del d赤a actual con los datos de los estudiantes y cursos
$query = '
SELECT 
    ec.id,
    INITCAP(e.nombre1 || \' \' || e.nombre2 || \' \' || e.apellido1 || \' \' || e.apellido2) AS nombre_estudiante,
    INITCAP(c.descripcion) AS curso_nombre,
    ec.fecha,
    ec.porcentaje
FROM 
    "public"."estudiantes_cursos" ec
JOIN 
    "public"."estudiantes" e ON ec.estudiante_id = e.id
JOIN 
    "public"."cursos" c ON ec.curso_id = c.id
WHERE 
    ec.enviado = false 
ORDER BY 
    ec.id DESC';

// Ejecutar la consulta
$result = pg_query($conexion, $query);

// Verificar si la consulta se ejecut車 correctamente
if (!$result) {
    echo "Error al ejecutar la consulta.";
    exit;
}

// Crear un archivo temporal para almacenar los datos CSV
$tmpFile = tempnam(sys_get_temp_dir(), 'csv');

// Abrir el archivo temporal para escritura
$file = fopen($tmpFile, 'w');

// Escribir la marca de orden de bytes (BOM) para UTF-8
fwrite($file, "\xEF\xBB\xBF");

// Escribir los encabezados de las columnas en el archivo CSV
$headers = array('ID', 'Nombre Completo del Estudiante', 'Curso', 'Fecha', 'Porcentaje');
fputcsv($file, $headers, ',');

// Escribir los datos de la consulta en el archivo CSV
while ($row = pg_fetch_assoc($result)) {
    fputcsv($file, $row, ',');
}

// Cerrar el archivo
fclose($file);

// Configurar los encabezados para la descarga del archivo
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="estudiantes_cursos.csv"');
header('Content-Length: ' . filesize($tmpFile));

// Leer el archivo y enviarlo al navegador
readfile($tmpFile);

// Eliminar el archivo temporal
unlink($tmpFile);
?>
