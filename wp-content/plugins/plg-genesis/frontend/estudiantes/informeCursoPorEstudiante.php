<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta seg¨²n tu estructura de directorios

// Verificar si el usuario no est¨¢ autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la p¨¢gina de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si se proporcionÃ³ el ID del estudiante
if (isset($_GET['id'])) {
    $id_estudiante = $_GET['id'];

    // Consulta SQL para obtener los detalles del estudiante y sus cursos
    $query_detalle = "SELECT * FROM estudiantes WHERE id = '$id_estudiante'";
    $resultado_detalle = pg_query($conexion, $query_detalle);

    $query_cursos = "SELECT ec.*, c.descripcion 
                     FROM estudiantes_cursos ec 
                     INNER JOIN cursos c ON ec.curso_id = c.id 
                     WHERE ec.estudiante_id = '$id_estudiante'";
    $resultado_cursos = pg_query($conexion, $query_cursos);

    // Verificar si se encontraron detalles del estudiante
    if ($resultado_detalle && pg_num_rows($resultado_detalle) > 0) {
        $detalle_estudiante = pg_fetch_assoc($resultado_detalle);

        // Configurar la cabecera para la descarga del archivo CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="estudiante_' . $detalle_estudiante['id_estudiante'] . '_cursos.csv"');

        $output = fopen('php://output', 'w');

        // Escribir el encabezado del archivo CSV
        fputcsv($output, array('ID Estudiante', 'Documento', 'Nombre', 'Apellido', 'Celular', 'Email', 'Ciudad', 'Iglesia'), ';');
        fputcsv($output, array(
            $detalle_estudiante['id_estudiante'],
            $detalle_estudiante['doc_identidad'],
            $detalle_estudiante['nombre1'] . ' ' . $detalle_estudiante['nombre2'],
            $detalle_estudiante['apellido1'] . ' ' . $detalle_estudiante['apellido2'],
            $detalle_estudiante['celular'],
            $detalle_estudiante['email'],
            $detalle_estudiante['ciudad'],
            $detalle_estudiante['iglesia']
        ), ';');

        // Espacio en blanco
        fputcsv($output, array(), ';');

        // Encabezado para los cursos
        fputcsv($output, array('Curso', 'Porcentaje', 'Fecha'), ';');

        // Escribir los cursos realizados por el estudiante
        if ($resultado_cursos && pg_num_rows($resultado_cursos) > 0) {
            while ($curso = pg_fetch_assoc($resultado_cursos)) {
                fputcsv($output, array($curso['descripcion'], $curso['porcentaje'], $curso['fecha']), ';');
            }
        } else {
            fputcsv($output, array('No se encontraron cursos realizados por este estudiante.'), ';');
        }

        fclose($output);
        exit;
    } else {
        echo 'No se encontraron detalles para este estudiante.';
    }
} else {
    echo 'No se proporcionÃ³ un ID de estudiante vÃ¡lido.';
}

// Cerrar la conexiÃ³n a la base de datos
pg_close($conexion);
?>
