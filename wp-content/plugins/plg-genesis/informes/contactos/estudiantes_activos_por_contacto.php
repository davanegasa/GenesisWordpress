<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/templates/informes_emmaus.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Validar que el parámetro contacto_id esté presente
if (!isset($_GET['contacto_id']) || empty($_GET['contacto_id'])) {
    die('El contacto_id es obligatorio.');
}

$contacto_id = $_GET['contacto_id'];

// Consulta SQL para obtener los datos
$sql = "
SELECT 
    e.id AS estudiante_id,
    e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 AS nombre_completo,
    e.email,
    e.celular,
    COUNT(c.id) AS cantidad_cursos,
    AVG(ec.porcentaje) AS promedio_notas,
    MAX(ec.fecha) AS ultima_fecha_entrega,
    e.fecha_registro as fecha_registro
FROM 
    estudiantes e
JOIN 
    estudiantes_cursos ec ON e.id = ec.estudiante_id
JOIN 
    cursos c ON ec.curso_id = c.id
WHERE 
    e.id_contacto = $1
GROUP BY 
    e.id, e.nombre1, e.nombre2, e.apellido1, e.apellido2, e.email, e.celular
ORDER BY 
    ultima_fecha_entrega DESC;
";

$result = pg_query_params($conexion, $sql, [$contacto_id]);

if (!$result) {
    die('Error en la consulta: ' . pg_last_error($conexion));
}

// Clasificar estudiantes en grupos según la última fecha de entrega
$grupos = [
    '0-3 meses' => [],
    '3-6 meses' => [],
    '6-12 meses' => [],
    '1-2 años' => [],
    'Inactivos (> 2 años)' => []
];

$currentDate = new DateTime();

while ($row = pg_fetch_assoc($result)) {
    $ultimaFecha = new DateTime($row['ultima_fecha_entrega']);
    $diferenciaMeses = $currentDate->diff($ultimaFecha)->m + ($currentDate->diff($ultimaFecha)->y * 12);

    if ($diferenciaMeses <= 3) {
        $grupos['0-3 meses'][] = $row;
    } elseif ($diferenciaMeses <= 6) {
        $grupos['3-6 meses'][] = $row;
    } elseif ($diferenciaMeses <= 12) {
        $grupos['6-12 meses'][] = $row;
    } elseif ($diferenciaMeses <= 24) {
        $grupos['1-2 años'][] = $row;
    } else {
        $grupos['Inactivos (> 2 años)'][] = $row;
    }
}

// Crear instancia del template de Emmaus
$pdf = new InformeEmmausTemplate("Listado de estudiantes activos por contacto");
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión Emmaus');
$pdf->SetTitle('Lista de Estudiantes activos por contacto');
$pdf->SetSubject('Informe generado por Emmaus');
$pdf->SetKeywords('PDF, estudiantes, informe, Emmaus');

// Configurar el template
$pdf->SetHeaderData(
    PDF_HEADER_LOGO,
    PDF_HEADER_LOGO_WIDTH,
    'Lista de Estudiantes',
    "Contacto: $contacto_id"
);
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage('L');

// Escribir contenido
foreach ($grupos as $grupo => $estudiantes) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Write(0, $grupo, '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(2);

    if (empty($estudiantes)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, 'No hay estudiantes en este grupo.', '', 0, 'L', true, 0, false, false, 0);
    } else {
        $tbl = '<table border="1" cellpadding="4">';
        $tbl .= '<tr style="background-color:#f2f2f2;">
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Celular</th>
                    <th>Cantidad Cursos</th>
                    <th>Promedio</th>
                    <th>Última Fecha</th>
                    <th>Fecha Registro</th>
                </tr>';
        foreach ($estudiantes as $estudiante) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($estudiante['nombre_completo']) . '</td>
                        <td>' . htmlspecialchars($estudiante['email']) . '</td>
                        <td>' . htmlspecialchars($estudiante['celular']) . '</td>
                        <td>' . htmlspecialchars($estudiante['cantidad_cursos']) . '</td>
                        <td>' . number_format($estudiante['promedio_notas'], 2) . '</td>
                        <td>' . htmlspecialchars($estudiante['ultima_fecha_entrega']) . '</td>
                        <td>' . htmlspecialchars($estudiante['fecha_registro']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    }

    $pdf->Ln(5); // Espacio entre grupos
}

// Salida del PDF
$pdf->Output('informe_estudiantes.pdf', 'I');