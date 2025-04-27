<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/templates/informes_emmaus.php'); 

// Verificar si se ha proporcionado el ID del estudiante
if (isset($_GET['id'])) {
    $id_estudiante_url = $_GET['id'];

    // Consulta SQL para obtener el id_estudiante de la tabla estudiantes
    $query_estudiante = "SELECT id_estudiante FROM estudiantes WHERE id = '$id_estudiante_url'";
    $resultado_estudiante = pg_query($conexion, $query_estudiante);
    $estudiante = pg_fetch_assoc($resultado_estudiante);

    if ($estudiante && isset($estudiante['id_estudiante'])) {
        $id_estudiante = $estudiante['id_estudiante'];

        // Consulta SQL para obtener los cursos del estudiante
        $query = "
            SELECT c.consecutivo, c.nombre, c.nivel_id, ec.porcentaje, ec.fecha 
            FROM estudiantes_cursos ec
            INNER JOIN cursos c ON ec.curso_id = c.id 
            WHERE ec.estudiante_id = '$id_estudiante_url'
            ORDER BY c.consecutivo ASC";

        $resultado = pg_query($conexion, $query);

        if ($resultado && pg_num_rows($resultado) > 0) {
            // Nombre del archivo CSV usando el id_estudiante de la tabla estudiantes
            $filename = "historial_cursos_estudiante_" . $id_estudiante . ".csv";

            // Encabezados HTTP para forzar la descarga del archivo CSV y asegurar la compatibilidad con Excel
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo "\xEF\xBB\xBF"; // Añadir el BOM para que Excel detecte UTF-8

            // Abrir un archivo en la salida (output) para escribir el contenido
            $output = fopen('php://output', 'w');

            // Escribir la primera fila del archivo con los nombres de las columnas
            fputcsv($output, array('Consecutivo', 'Nombre del Curso', 'Nivel', 'Porcentaje Completado', 'Fecha de Entrega'), ';');

            // Escribir los datos de los cursos del estudiante
            while ($row = pg_fetch_assoc($resultado)) {
                fputcsv($output, array(
                    $row['consecutivo'],        // Agregamos el consecutivo del curso
                    $row['nombre'],             // Nombre del curso
                    'Nivel ' . $row['nivel_id'],// Nivel del curso
                    $row['porcentaje'] . '%',   // Porcentaje completado
                    $row['fecha']               // Fecha de entrega
                ), ';');
            }

            // Cerrar el archivo de salida
            fclose($output);
            exit;
        } else {
            echo "No se encontraron cursos para este estudiante.";
        }
    } else {
        echo "Estudiante no encontrado.";
    }
} else {
    echo "ID de estudiante no proporcionado.";
}
?>