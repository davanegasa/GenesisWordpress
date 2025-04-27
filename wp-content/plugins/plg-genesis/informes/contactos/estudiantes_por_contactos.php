<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/templates/informes_emmaus.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Función para manejar errores
function responderError($mensaje, $codigoHttp = 400) {
    if (!headers_sent()) {
        header('Content-Type: application/json', true, $codigoHttp);
    }
    die(json_encode(['error' => $mensaje]));
}

// Verificar que el contacto_id se pase como parámetro
if (!isset($_GET['contacto_id']) || empty($_GET['contacto_id'])) {
    responderError("El ID del contacto es requerido.");
}

$contactoId = intval($_GET['contacto_id']);

// Consultar los detalles del contacto
$queryContacto = "
    SELECT nombre, iglesia, code
    FROM contactos
    WHERE id = $1
";
$resultContacto = pg_query_params($conexion, $queryContacto, [$contactoId]);

if (!$resultContacto || pg_num_rows($resultContacto) === 0) {
    responderError("No se encontró información del contacto especificado." . "$queryContacto", 404);
}

$contacto = pg_fetch_assoc($resultContacto);
pg_free_result($resultContacto);

// Consultar los estudiantes directamente de la base de datos
$queryEstudiantes = "
    SELECT 
        e.id AS id_estudiante,
        CONCAT_WS(' ', e.nombre1, e.nombre2, e.apellido1, e.apellido2) AS nombre_completo,
        e.doc_identidad,
        e.celular,
        e.email
    FROM estudiantes e
    WHERE e.id_contacto = $1
    ORDER BY e.id
";

$resultEstudiantes = pg_query_params($conexion, $queryEstudiantes, [$contactoId]);

if (!$resultEstudiantes) {
    responderError("Error al consultar los estudiantes desde la base de datos.");
}

$estudiantes = [];
while ($row = pg_fetch_assoc($resultEstudiantes)) {
    $estudiantes[] = [
        'id_estudiante' => $row['id_estudiante'],
        'nombre_completo' => $row['nombre_completo'],
        'doc_identidad' => $row['doc_identidad'],
        'celular' => $row['celular'],
        'email' => $row['email']
    ];
}

pg_free_result($resultEstudiantes);
pg_close($conexion);

// Verificar si hay estudiantes
if (empty($estudiantes)) {
    responderError("No se encontraron estudiantes para el contacto especificado.", 404);
}

// Crear instancia del template de Emmaus
$pdf = new InformeEmmausTemplate("Listado de estudiantes.");
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión Emmaus');
$pdf->SetTitle('Lista de Estudiantes');
$pdf->SetSubject('Informe generado por Emmaus');
$pdf->SetKeywords('PDF, estudiantes, informe, Emmaus');

// Configurar el template
$pdf->SetHeaderData(
    PDF_HEADER_LOGO,
    PDF_HEADER_LOGO_WIDTH,
    'Lista de Estudiantes',
    "Contacto: {$contacto['nombre']} | Código: {$contacto['code']}"
);
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 10);

// Agregar una página
$pdf->AddPage('L');

// Crear contenido del encabezado estilizado
$htmlHeader = '
    <div style="border: 2px solid #2E4053; border-radius: 8px; padding: 15px; margin-bottom: 20px; background-color: #f4f6f9;">
        <table style="width: 100%; font-size: 12px; line-height: 1.5; color: #2E4053;">
            <tr>
                <td style="width: 30%; font-weight: bold; text-align: right; padding-right: 10px;">Nombre:</td>
                <td style="width: 70%; text-align: left;">' . htmlspecialchars($contacto['nombre']) . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding-right: 10px;">Iglesia:</td>
                <td style="text-align: left;">' . htmlspecialchars($contacto['iglesia']) . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding-right: 10px;">Código:</td>
                <td style="text-align: left;">' . htmlspecialchars($contacto['code']) . '</td>
            </tr>
        </table>
    </div>';
$pdf->writeHTML($htmlHeader, true, false, true, false, '');

// Crear contenido de la tabla
$htmlTable = '<h2 style="color: #2E4053;">Lista de Estudiantes</h2>';
$htmlTable .= '<table border="1" cellpadding="5">';
$htmlTable .= '<thead>
                <tr style="background-color: #f8f9fa; color: #2E4053;">
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Celular</th>
                    <th>Email</th>
                </tr>
              </thead>';
$htmlTable .= '<tbody>';

foreach ($estudiantes as $estudiante) {
    $htmlTable .= '<tr>
                    <td>' . htmlspecialchars($estudiante['id_estudiante']) . '</td>
                    <td>' . htmlspecialchars($estudiante['nombre_completo']) . '</td>
                    <td>' . htmlspecialchars($estudiante['doc_identidad']) . '</td>
                    <td>' . htmlspecialchars($estudiante['celular']) . '</td>
                    <td>' . htmlspecialchars($estudiante['email']) . '</td>
                  </tr>';
}

$htmlTable .= '</tbody>';
$htmlTable .= '</table>';

// Agregar contenido al PDF con el estilo del template
$pdf->writeHTML($htmlTable, true, false, true, false, '');

// Generar el archivo PDF
$pdf->Output('Lista_Estudiantes.pdf', 'I');