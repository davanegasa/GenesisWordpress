<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/tcpdf.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../libs/TCPDF/templates/informes_emmaus.php'); 
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Crear instancia de InformeEmmausTemplate
$pdf = new InformeEmmausTemplate("Listado de Contactos.");
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión');
$pdf->SetTitle('Informe de Contactos');
$pdf->SetHeaderData('', 0, 'Informe de Contactos', 'Generado por el Sistema Emmaus');
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage('L');

// Estilo para el contenido
$styleHeader = '<style>
    th { background-color: #f2f2f2; font-weight: bold; font-size: 11px; text-align: center; }
    td { font-size: 10px; text-align: left; padding: 5px; }
</style>';

// Consultar los contactos desde la base de datos
$sql = "SELECT code, nombre, iglesia, email, celular, ciudad FROM contactos ORDER BY code ASC";
$result = pg_query($conexion, $sql);

if (!$result) {
    die('Error al obtener los datos de contactos: ' . pg_last_error($conexion));
}

$contacts = pg_fetch_all($result);

// Verificar si hay datos
if (!$contacts) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, 'No se encontraron contactos para generar el informe.', '', 0, 'C', true, 0, false, false, 0);
    $pdf->Output('informe_contactos.pdf', 'I');
    exit;
}

$html = $styleHeader . '<table border="0" cellspacing="0" cellpadding="4" width="100%">
    <thead>
        <tr>
            <th width="10%">Code</th>
            <th width="25%">Nombre</th>
            <th width="20%">Iglesia</th>
            <th width="20%">Email</th>
            <th width="15%">Celular</th>
            <th width="10%">Ciudad</th>
        </tr>
    </thead>
    <tbody>';

foreach ($contacts as $contact) {
    $html .= '<tr>
        <td width="10%">' . htmlspecialchars($contact['code']) . '</td>
        <td width="25%">' . htmlspecialchars($contact['nombre']) . '</td>
        <td width="20%">' . htmlspecialchars($contact['iglesia'] ?: '-') . '</td>
        <td width="20%">' . htmlspecialchars($contact['email'] ?: '-') . '</td>
        <td width="15%">' . htmlspecialchars($contact['celular'] ?: '-') . '</td>
        <td width="10%">' . htmlspecialchars($contact['ciudad'] ?: '-') . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// Escribir tabla en el PDF
$pdf->SetFont('helvetica', '', 10);
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF al navegador
$pdf->Output('informe_contactos.pdf', 'I');
?>