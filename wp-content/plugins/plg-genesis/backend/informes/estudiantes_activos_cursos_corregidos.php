<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Validar que el año esté presente
if (!isset($_GET['year']) || !is_numeric($_GET['year'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El año es obligatorio y debe ser un número válido.']);
    exit;
}

$year = intval($_GET['year']);
$currentYear = date('Y');
$currentMonth = date('m');

// Verificar que el año no sea mayor al actual
if ($year > $currentYear) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se permiten informes para años futuros.']);
    exit;
}

// Restringir la generación de meses si es el año actual
$endDate = $year === intval($currentYear) ? "$year-$currentMonth-01" : "$year-12-01";

$query = "
WITH meses AS (
    SELECT GENERATE_SERIES('$year-01-01'::DATE, '$endDate'::DATE, '1 month'::INTERVAL) AS mes
),
estudiantes_activos AS (
    SELECT 
        TO_CHAR(mes, 'YYYY-MM') AS mes,
        COUNT(DISTINCT ec.estudiante_id) AS activos
    FROM 
        meses
    LEFT JOIN 
        estudiantes_cursos ec ON ec.fecha BETWEEN (mes - INTERVAL '1 year') AND (mes + INTERVAL '1 month' - INTERVAL '1 day')
    GROUP BY mes
),
cursos_correjidos AS (
    SELECT 
        TO_CHAR(ec.fecha, 'YYYY-MM') AS mes,
        COUNT(ec.id) AS corregidos
    FROM 
        estudiantes_cursos ec
    WHERE 
        ec.fecha BETWEEN '$year-01-01' AND '$endDate'::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(ec.fecha, 'YYYY-MM')
),
estudiantes_registrados AS (
    SELECT 
        TO_CHAR(estudiantes.fecha_registro, 'YYYY-MM') AS mes,
        COUNT(*) AS nuevos_estudiantes
    FROM 
        estudiantes
    WHERE 
        estudiantes.fecha_registro BETWEEN '$year-01-01' AND '$endDate'::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(estudiantes.fecha_registro, 'YYYY-MM')
),
contactos_activos AS (
    SELECT 
        TO_CHAR(mes, 'YYYY-MM') AS mes,
        COUNT(DISTINCT c.id) AS contactos_activos
    FROM 
        meses
    LEFT JOIN 
        estudiantes e ON e.id_contacto IS NOT NULL
    LEFT JOIN 
        estudiantes_cursos ec ON ec.estudiante_id = e.id
    LEFT JOIN 
        contactos c ON c.id = e.id_contacto
    WHERE 
        ec.fecha BETWEEN (mes - INTERVAL '1 year') AND (mes + INTERVAL '1 month' - INTERVAL '1 day')
    GROUP BY mes
),
contactos_nuevos AS (
    SELECT 
        TO_CHAR(contactos.fecha_registro, 'YYYY-MM') AS mes,
        COUNT(*) AS nuevos_contactos
    FROM 
        contactos
    WHERE 
        contactos.fecha_registro BETWEEN '$year-01-01' AND '$endDate'::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(contactos.fecha_registro, 'YYYY-MM')
)
SELECT 
    COALESCE(ea.mes, cc.mes, er.mes, ca.mes, cn.mes) AS mes,
    COALESCE(ea.activos, 0) AS estudiantes_activos,
    COALESCE(cc.corregidos, 0) AS cursos_correjidos,
    COALESCE(er.nuevos_estudiantes, 0) AS estudiantes_registrados,
    COALESCE(ca.contactos_activos, 0) AS contactos_activos,
    COALESCE(cn.nuevos_contactos, 0) AS contactos_registrados
FROM 
    estudiantes_activos ea
FULL OUTER JOIN 
    cursos_correjidos cc ON ea.mes = cc.mes
FULL OUTER JOIN 
    estudiantes_registrados er ON COALESCE(ea.mes, cc.mes) = er.mes
FULL OUTER JOIN 
    contactos_activos ca ON COALESCE(ea.mes, cc.mes, er.mes) = ca.mes
FULL OUTER JOIN 
    contactos_nuevos cn ON COALESCE(ea.mes, cc.mes, er.mes, ca.mes) = cn.mes
ORDER BY 
    mes;
";

$result = pg_query($conexion, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta: ' . pg_last_error($conexion)]);
    exit;
}

$data = pg_fetch_all($result);
echo json_encode(['success' => true, 'data' => $data]);