<?php

require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/templates/informes_emmaus.php'); 

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

$id_contacto = isset($_GET['id_contacto']) ? $_GET['id_contacto'] : null;
$id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : null;
$incluir_todos = isset($_GET['incluir_todos']) && $_GET['incluir_todos'] === 'true';

// Asegurarse de que al menos uno de los parámetros esté presente
if (!$id_contacto && !$id_estudiante) {
    die("Debe proporcionar un ID de contacto o un ID de estudiante.");
}

// Si se proporciona un id_estudiante, generar informe para un solo estudiante
if ($id_estudiante) {
    // Consulta para un solo estudiante
    $query_estudiantes = "
        SELECT e.*, c.nombre as contacto_nombre, c.code as contacto_code
        FROM estudiantes e
        JOIN contactos c ON e.id_contacto = c.id
        WHERE e.id = $id_estudiante
    ";
} else {
    // Consulta para todos los estudiantes del contacto
    $query_estudiantes = "
        SELECT e.*, c.nombre as contacto_nombre, c.code as contacto_code
        FROM estudiantes e
        JOIN contactos c ON e.id_contacto = c.id
        WHERE e.id_contacto = $id_contacto
    ";
}

$result_estudiantes = pg_query($conexion, $query_estudiantes);

if (!$result_estudiantes) {
    die('Error en la consulta de estudiantes: ' . pg_last_error($conexion));
}

// Comenzar a escribir el contenido del PDF
if ($result_estudiantes && pg_num_rows($result_estudiantes) > 0) {
    $contacto = pg_fetch_assoc($result_estudiantes);
    $contacto_nombre = $contacto['contacto_nombre'];
    $contacto_code = $contacto['contacto_code'];

    $titulo_personalizado = 'Reporte de Estudiantes y Cursos';
    $pdf = new InformeEmmausTemplate($titulo_personalizado);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Emmaus');
    $pdf->SetTitle('Lista de Estudiantes y Cursos');
    $pdf->SetHeaderData('', 0, 'Lista de Estudiantes y Cursos Realizados', "Contacto: $contacto_code - $contacto_nombre");
    
    // Configurar el PDF
    $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    do {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Código: " . $contacto['id_estudiante'], 0, 1);
        $pdf->Cell(0, 10, "Estudiante: " . $contacto['nombre1'] . ' ' . $contacto['apellido1'], 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, "Email: " . $contacto['email'], 0, 1);
        $pdf->Cell(0, 10, "Celular: " . $contacto['celular'], 0, 1);
        $pdf->Ln(5);

        // Consulta para obtener los cursos del estudiante
        $query_cursos = $incluir_todos ? "
            SELECT cursos.nombre AS curso_nombre, cursos.descripcion AS descripcion, niveles.nombre AS nivel_nombre, cursos.consecutivo,
                COALESCE(estudiantes_cursos.porcentaje, 0) AS porcentaje, estudiantes_cursos.fecha AS fecha
            FROM cursos
            JOIN niveles ON cursos.nivel_id = niveles.id
            LEFT JOIN estudiantes_cursos ON estudiantes_cursos.curso_id = cursos.id AND estudiantes_cursos.estudiante_id = " . $contacto['id'] . "
            ORDER BY niveles.id, cursos.consecutivo
        " : "
            SELECT cursos.nombre AS curso_nombre, cursos.descripcion AS descripcion, niveles.nombre AS nivel_nombre, cursos.consecutivo,
                estudiantes_cursos.porcentaje, estudiantes_cursos.fecha
            FROM estudiantes_cursos
            JOIN cursos ON estudiantes_cursos.curso_id = cursos.id
            JOIN niveles ON cursos.nivel_id = niveles.id
            WHERE estudiantes_cursos.estudiante_id = " . $contacto['id'] . "
            ORDER BY niveles.id, cursos.consecutivo
        ";

        // Ejecutar la consulta de cursos
        $resultado_cursos = pg_query($conexion, $query_cursos);

        if (!$resultado_cursos) {
            $pdf->Cell(0, 10, 'Error al obtener cursos: ' . pg_last_error($conexion), 0, 1);
            continue;
        }

        // Verificar si hay cursos y mostrarlos
        if (pg_num_rows($resultado_cursos) > 0) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Cursos Realizados:', 0, 1);
            $pdf->Ln(2);

            // Encabezados de la tabla
            $pdf->SetFont('helvetica', '', 10);
            $nivel_actual = null;
            $contador = 1;

            while ($curso = pg_fetch_assoc($resultado_cursos)) {
                if ($nivel_actual !== $curso['nivel_nombre']) {
                    if ($nivel_actual !== null) {
                        $pdf->Ln(4);
                    }
                    
                    $nivel_actual = $curso['nivel_nombre'];
                    $pdf->SetFont('helvetica', 'B', 11);
                    $pdf->Cell(0, 10, 'Nivel: ' . $nivel_actual, 0, 1, 'L');
                    $pdf->Ln(2);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->SetFillColor(240, 240, 240);
                    $pdf->Cell(10, 8, '#', 1, 0, 'C', 1);
                    $pdf->Cell(90, 8, 'Curso', 1, 0, 'C', 1);
                    $pdf->Cell(40, 8, 'Fecha', 1, 0, 'C', 1);
                    $pdf->Cell(30, 8, 'Progreso (%)', 1, 1, 'C', 1);
                }

                $fecha_curso = $curso['fecha'] === null ? '-' : date('d/m/Y', strtotime($curso['fecha']));
                $progreso_curso = $curso['porcentaje'] == 0 ? '-' : number_format($curso['porcentaje'], 2);

                if ($curso['porcentaje'] == 0) {
                    $pdf->SetFillColor(255, 200, 200);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $pdf->Cell(10, 8, $curso['consecutivo'], 1, 0, 'C', 1);
                $pdf->Cell(90, 8, $curso['descripcion'], 1, 0, '', 1);
                $pdf->Cell(40, 8, $fecha_curso, 1, 0, 'C', 1);
                $pdf->Cell(30, 8, $progreso_curso, 1, 1, 'C', 1);
            }
        } else {
            $pdf->Cell(0, 10, 'No hay cursos registrados para este estudiante.', 0, 1);
        }

        $pdf->AddPage();
    } while ($contacto = pg_fetch_assoc($result_estudiantes));

    $pdf->Output('lista_estudiantes_cursos.pdf', 'I');
    exit;
} else {
    echo "No se encontraron estudiantes para este contacto.";
}

pg_close($conexion);
?>