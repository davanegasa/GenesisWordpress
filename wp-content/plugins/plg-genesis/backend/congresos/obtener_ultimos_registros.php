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

// Consulta para obtener los últimos 10 registros
$query = "
    SELECT * FROM (
        -- Estudiantes
        SELECT 
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 as nombre,
            ac.taller_asignado as taller
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN estudiantes e ON ac.id_estudiante = e.id
        WHERE bc.id_congreso = $1
        
        UNION ALL
        
        -- Asistentes externos
        SELECT 
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            ae.nombre,
            ac.taller_asignado as taller
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN asistentes_externos ae ON ac.id_asistente = ae.id
        WHERE bc.id_congreso = $1
    ) as registros
    ORDER BY fecha_registro DESC
    LIMIT 10
";

$result = pg_query_params($conexion, $query, [$id_congreso]);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener registros']);
    exit;
}

$registros = [];
while ($row = pg_fetch_assoc($result)) {
    $registros[] = $row;
}

header('Content-Type: application/json');
echo json_encode($registros); 