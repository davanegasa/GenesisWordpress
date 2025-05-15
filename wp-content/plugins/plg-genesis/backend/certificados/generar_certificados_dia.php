<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once(__DIR__ . '/../../../../../wp-load.php');
    require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
    require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php');

    if (!is_user_logged_in()) {
        throw new Exception('No autorizado');
    }

    // Verificar que se recibieron los parámetros de fecha
    if (!isset($_GET['dia']) || !isset($_GET['mes']) || !isset($_GET['anio'])) {
        throw new Exception('Parámetros de fecha no válidos');
    }

    $dia = intval($_GET['dia']);
    $mes = intval($_GET['mes']);
    $anio = intval($_GET['anio']);

    if (!checkdate($mes, $dia, $anio)) {
        throw new Exception('Fecha no válida');
    }

    // Consulta para obtener los datos de los cursos
    $query = "
        SELECT 
            ec.id as estudiante_curso_id,
            e.id as estudiante_id,
            c.descripcion as nombre_curso,
            CONCAT(e.nombre1, ' ', COALESCE(e.nombre2, ''), ' ', e.apellido1, ' ', COALESCE(e.apellido2, '')) as nombre_estudiante,
            e.celular,
            co.nombre as nombre_contacto,
            ec.porcentaje as nota,
            ec.fecha
        FROM estudiantes_cursos ec
        JOIN estudiantes e ON e.id = ec.estudiante_id
        JOIN cursos c ON c.id = ec.curso_id
        LEFT JOIN contactos co ON e.id_contacto = co.id
        WHERE DATE(ec.fecha) = $1
        ORDER BY e.apellido1, e.nombre1
    ";

    $fecha = date('Y-m-d', mktime(0, 0, 0, $mes, $dia, $anio));
    $result = pg_query_params($conexion, $query, array($fecha));

    if (!$result) {
        throw new Exception('Error en la consulta: ' . pg_last_error($conexion));
    }

    if (pg_num_rows($result) == 0) {
        throw new Exception('No se encontraron cursos para la fecha: ' . $fecha);
    }

    // Crear nuevo documento PDF
    class MYPDF extends TCPDF {
        public function Header() {
            // Logo
            $image_file = plugin_dir_path(__FILE__) . '/../../assets/img/logo.png';
            if (file_exists($image_file)) {
                $this->Image($image_file, 10, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
            
            // Título
            $this->SetFont('helvetica', 'B', 20);
        }
    }

    // Función para obtener el nombre del mes en español
    function obtenerNombreMes($mes) {
        $meses = array(
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        );
        return $meses[$mes];
    }

    // Crear nuevo documento PDF
    $pdf = new MYPDF('L', 'mm', array(130, 200), true, 'UTF-8', false);

    // Establecer información del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Genesis');
    $pdf->SetTitle('Certificados del ' . $dia . '/' . $mes . '/' . $anio);

    // Establecer márgenes más compactos
    $pdf->SetMargins(10, 5, 10);
    $pdf->SetHeaderMargin(2);
    $pdf->SetFooterMargin(5);

    // Establecer saltos de página automáticos
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Establecer factor de escala de imagen
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $certificados_generados = 0;
    while ($curso = pg_fetch_assoc($result)) {
        // Agregar una nueva página para cada certificado
        $pdf->AddPage();
        
        // Establecer fuente
        $pdf->SetFont('helvetica', 'B', 20);

        // Formatear fecha
        $dia_cert = date('j', strtotime($curso['fecha']));
        $mes_cert = obtenerNombreMes(date('n', strtotime($curso['fecha'])));
        $anio_cert = date('Y', strtotime($curso['fecha']));

        // Contenido del certificado
        $html = '
        <style>
            .certificado {
                text-align: center;
                line-height: 1.4;
                padding: 5px;
            }
            .nombre {
                font-size: 24px;
                font-weight: bold;
                color: #1a3b89;
                margin: 15px 0;
            }
            .curso {
                font-size: 20px;
                color: #333;
                margin: 12px 0;
            }
            .porcentaje {
                font-size: 16px;
                color: #444;
                margin: 10px 0;
            }
            .fecha {
                font-size: 12px;
                color: #666;
                margin-top: 10px;
                font-style: italic;
            }
        </style>

        <div class="certificado">            
            <p class="nombre">' . htmlspecialchars($curso['nombre_estudiante']) . '</p>
            
            <p class="curso">' . htmlspecialchars($curso['nombre_curso']) . '</p>
            
            <p class="porcentaje">
                Porcentaje obtenido: ' . htmlspecialchars($curso['nota']) . '%
            </p>

            <p class="fecha">
                A los ' . $dia_cert . ' días del mes de ' . $mes_cert . ' de ' . $anio_cert . '
            </p>
        </div>
        ';

        // Escribir HTML en el PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        $certificados_generados++;
    }

    if ($certificados_generados === 0) {
        throw new Exception('No se generaron certificados');
    }

    // Cerrar y generar el PDF
    $pdf->Output('certificados_' . $dia . '_' . $mes . '_' . $anio . '.pdf', 'I');

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="color: red; font-family: Arial, sans-serif; padding: 20px;">';
    echo '<h2>Error al generar los certificados:</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Detalles técnicos:</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
?> 