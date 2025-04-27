<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener el ID del congreso
$id_congreso = isset($_GET['id_congreso']) ? intval($_GET['id_congreso']) : 0;

if (!$id_congreso) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de congreso no válido']);
    exit;
}

// Consulta para obtener los datos de los inscritos
$query = "
    SELECT * FROM (
        -- Estudiantes inscritos
        SELECT 
            bc.numero_boleta,
            bc.estado,
            e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 as nombre,
            e.doc_identidad as identificacion,
            e.email,
            e.celular as telefono,
            e.iglesia as congregacion,
            ac.taller_asignado as taller,
            bc.fecha_registro as fecha_inscripcion,
            'Estudiante' as tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN estudiantes e ON ac.id_estudiante = e.id
        WHERE bc.id_congreso = $1
        
        UNION ALL
        
        -- Asistentes externos inscritos
        SELECT 
            bc.numero_boleta,
            bc.estado,
            ae.nombre,
            ae.identificacion,
            ae.email,
            ae.telefono,
            ae.congregacion,
            ac.taller_asignado as taller,
            bc.fecha_registro as fecha_inscripcion,
            'Asistente Externo' as tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN asistentes_externos ae ON ac.id_asistente = ae.id
        WHERE bc.id_congreso = $1
    ) as inscritos
    ORDER BY fecha_inscripcion DESC, nombre
";

$result = pg_query_params($conexion, $query, [$id_congreso]);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los datos']);
    exit;
}

// Obtener el nombre del congreso
$query_congreso = "SELECT nombre FROM congresos WHERE id = $1";
$result_congreso = pg_query_params($conexion, $query_congreso, [$id_congreso]);
$nombre_congreso = pg_fetch_result($result_congreso, 0, 0);

// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="inscritos_' . $nombre_congreso . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear el contenido del Excel
echo '<table border="1">';
echo '<tr>';
echo '<th>Número Boleta</th>';
echo '<th>Estado</th>';
echo '<th>Nombre</th>';
echo '<th>Cédula</th>';
echo '<th>Email</th>';
echo '<th>Teléfono</th>';
echo '<th>Congregación</th>';
echo '<th>Taller</th>';
echo '<th>Fecha Inscripción</th>';
echo '<th>Tipo</th>';
echo '</tr>';

while ($row = pg_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['numero_boleta']) . '</td>';
    echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
    echo '<td>' . htmlspecialchars($row['identificacion']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . htmlspecialchars($row['telefono']) . '</td>';
    echo '<td>' . htmlspecialchars($row['congregacion']) . '</td>';
    echo '<td>' . htmlspecialchars($row['taller']) . '</td>';
    echo '<td>' . date('d/m/Y H:i', strtotime($row['fecha_inscripcion'])) . '</td>';
    echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
    echo '</tr>';
}

echo '</table>'; 