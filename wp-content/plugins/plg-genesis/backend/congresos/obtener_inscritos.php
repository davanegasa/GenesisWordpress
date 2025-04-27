<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Configurar respuesta JSON
header('Content-Type: application/json');

// Obtener parámetros de DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$id_congreso = isset($_POST['id_congreso']) ? intval($_POST['id_congreso']) : 0;

// Preparar el patrón de búsqueda
$search_pattern = '%' . $search . '%';

// Primero obtener el total de registros sin filtros
$query_total = "
    SELECT COUNT(*) as total
    FROM boletas_congresos bc
    WHERE bc.id_congreso = $1
";
$result_total = pg_query_params($conexion, $query_total, [$id_congreso]);
$total_records = pg_fetch_result($result_total, 0, 0);

// Consulta principal con búsqueda y paginación
$query = "
    SELECT * FROM (
        -- Estudiantes inscritos
        SELECT
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            -- Datos del estudiante
            e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 AS nombre,
            e.doc_identidad AS identificacion,
            e.email,
            e.celular AS telefono,
            e.iglesia AS congregacion,
            ac.taller_asignado AS taller,
            'estudiante' AS tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN estudiantes e ON ac.id_estudiante = e.id
        WHERE bc.id_congreso = $1
        
        UNION ALL
        
        -- Asistentes externos inscritos
        SELECT
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            -- Datos del asistente externo
            ae.nombre,
            ae.identificacion,
            ae.email,
            ae.telefono,
            ae.congregacion,
            ac.taller_asignado AS taller,
            'externo' AS tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN asistentes_externos ae ON ac.id_asistente = ae.id
        WHERE bc.id_congreso = $1
    ) as inscritos
    WHERE 
        LOWER(nombre) LIKE LOWER($2) OR 
        LOWER(identificacion) LIKE LOWER($2) OR 
        LOWER(email) LIKE LOWER($2) OR 
        LOWER(telefono) LIKE LOWER($2) OR 
        LOWER(congregacion) LIKE LOWER($2) OR
        LOWER(numero_boleta) LIKE LOWER($2)
    ORDER BY fecha_registro DESC, nombre
    LIMIT $3 OFFSET $4
";

// Obtener registros filtrados
$result = pg_query_params($conexion, $query, [$id_congreso, $search_pattern, $length, $start]);

if (!$result) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => pg_last_error($conexion)
    ]);
    exit;
}

// Contar registros filtrados
$query_filtered = "
    SELECT COUNT(*) as filtered FROM (
        -- Estudiantes inscritos
        SELECT
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            -- Datos del estudiante
            e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 AS nombre,
            e.doc_identidad AS identificacion,
            e.email,
            e.celular AS telefono,
            e.iglesia AS congregacion,
            ac.taller_asignado AS taller,
            'estudiante' AS tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN estudiantes e ON ac.id_estudiante = e.id
        WHERE bc.id_congreso = $1
        
        UNION ALL
        
        -- Asistentes externos inscritos
        SELECT
            bc.numero_boleta,
            bc.estado,
            bc.fecha_registro,
            -- Datos del asistente externo
            ae.nombre,
            ae.identificacion,
            ae.email,
            ae.telefono,
            ae.congregacion,
            ac.taller_asignado AS taller,
            'externo' AS tipo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        JOIN asistentes_externos ae ON ac.id_asistente = ae.id
        WHERE bc.id_congreso = $1
    ) as inscritos
    WHERE 
        LOWER(nombre) LIKE LOWER($2) OR 
        LOWER(identificacion) LIKE LOWER($2) OR 
        LOWER(email) LIKE LOWER($2) OR 
        LOWER(telefono) LIKE LOWER($2) OR 
        LOWER(congregacion) LIKE LOWER($2) OR
        LOWER(numero_boleta) LIKE LOWER($2)
";

$result_filtered = pg_query_params($conexion, $query_filtered, [$id_congreso, $search_pattern]);
$total_filtered = pg_fetch_result($result_filtered, 0, 0);

// Preparar datos para DataTables
$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = [
        'numero_boleta' => $row['numero_boleta'],
        'estado' => $row['estado'],
        'nombre' => $row['nombre'],
        'identificacion' => $row['identificacion'],
        'email' => $row['email'],
        'telefono' => $row['telefono'],
        'congregacion' => $row['congregacion'],
        'taller' => $row['taller'],
        'fecha_inscripcion' => $row['fecha_registro'],
        'tipo' => $row['tipo']
    ];
}

// Consulta de diagnóstico
$diagnostic_query = "
    SELECT 
        ac.id as id_asistencia,
        bc.id as id_boleta,
        bc.numero_boleta,
        bc.fecha_registro,
        CASE 
            WHEN ac.id_estudiante IS NOT NULL THEN 'estudiante'
            ELSE 'externo'
        END as tipo
    FROM asistencias_congresos ac
    LEFT JOIN boletas_congresos bc ON bc.id_asistencia = ac.id
    WHERE ac.id_congreso = $1
    LIMIT 5;
";
$diagnostic_result = pg_query_params($conexion, $diagnostic_query, [$id_congreso]);

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => intval($total_records),
    'recordsFiltered' => intval($total_filtered),
    'data' => $data
]);