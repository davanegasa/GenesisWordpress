<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php');

if (!is_user_logged_in()) {
    http_response_code(403);
    die('No autorizado');
}

// Verificar que se recibió el ID del curso asignado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de curso asignado no válido');
}

$id_curso_asignado = intval($_GET['id']);

// Obtener los datos necesarios para el certificado
$query = "
    SELECT 
        e.nombre1, 
        e.nombre2, 
        e.apellido1, 
        e.apellido2,
        c.nombre as nombre_curso,
        c.descripcion as descripcion_curso,
        ec.fecha,
        ec.porcentaje,
        n.nombre as nivel
    FROM estudiantes_cursos ec
    JOIN estudiantes e ON e.id = ec.estudiante_id
    JOIN cursos c ON c.id = ec.curso_id
    JOIN niveles n ON n.id = c.nivel_id
    WHERE ec.id = $1
";

$result = pg_query_params($conexion, $query, array($id_curso_asignado));

if (!$result || pg_num_rows($result) == 0) {
    die('No se encontraron datos del certificado');
}

$datos = pg_fetch_assoc($result);

// Crear nuevo documento PDF
class MYPDF extends TCPDF {
    public function Header() {
        // Logo
        $image_file = plugin_dir_path(__FILE__) . '/../../assets/img/logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
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
$pdf->SetTitle('Certificado - ' . $datos['nombre_curso']);

// Establecer márgenes más compactos
$pdf->SetMargins(10, 5, 10);
$pdf->SetHeaderMargin(2);
$pdf->SetFooterMargin(5);

// Establecer saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Establecer factor de escala de imagen
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Agregar una página
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont('helvetica', 'B', 20);

// Formatear fecha para el nuevo formato
$dia_cert = date('j', strtotime($datos['fecha']));
$mes_cert = obtenerNombreMes(date('n', strtotime($datos['fecha'])));
$anio_cert = date('Y', strtotime($datos['fecha']));

// Nombre completo del estudiante
$nombre_completo = trim($datos['nombre1'] . ' ' . 
                       ($datos['nombre2'] ? $datos['nombre2'] . ' ' : '') . 
                       $datos['apellido1'] . ' ' . 
                       ($datos['apellido2'] ? $datos['apellido2'] : ''));

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
    <p class="nombre">' . htmlspecialchars($nombre_completo) . '</p>
    
    <p class="curso">' . htmlspecialchars($datos['nombre_curso']) . '</p>
    
    <p class="porcentaje">
        Porcentaje obtenido: ' . htmlspecialchars($datos['porcentaje']) . '%
    </p>

    <p class="fecha">
        A los ' . $dia_cert . ' días del mes de ' . $mes_cert . ' de ' . $anio_cert . '
    </p>
</div>
';

// Escribir HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Cerrar y generar el PDF
$pdf->Output('certificado_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nombre_completo) . '.pdf', 'I');
?> 