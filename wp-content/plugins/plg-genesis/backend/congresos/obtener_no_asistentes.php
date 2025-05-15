<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Configurar el encabezado para devolver siempre JSON
header('Content-Type: application/json');

// Validar que la solicitud sea POST con JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida, se esperaba JSON']);
    exit;
}

// Leer el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos obligatorios
if (!isset($data['id_congreso'])) {
    echo json_encode(['success' => false, 'error' => 'ID de congreso no proporcionado']);
    exit;
}

$id_congreso = intval($data['id_congreso']);

// Consulta para obtener estudiantes sin registro de llegada
$query = "
    WITH inscritos AS (
        SELECT 
            ac.id as id_asistencia,
            CASE 
                WHEN ac.id_estudiante IS NOT NULL THEN 'Estudiante'
                ELSE 'Externo'
            END as tipo,
            ac.id_estudiante,
            ac.id_asistente,
            ac.taller_asignado,
            bc.numero_boleta,
            bc.codigo_verificacion,
            CONCAT(bc.numero_boleta, bc.codigo_verificacion) as codigo_completo,
            bc.fecha_llegada
        FROM asistencias_congresos ac
        JOIN boletas_congresos bc ON bc.id_asistencia = ac.id
        WHERE ac.id_congreso = $1 
        AND bc.estado = 'usado'
        AND bc.fecha_registro IS NOT NULL
        AND bc.fecha_llegada IS NULL
    )
    SELECT 
        i.*,
        CASE 
            WHEN i.tipo = 'Estudiante' THEN 
                e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2
            ELSE 
                ae.nombre
        END as nombre,
        CASE 
            WHEN i.tipo = 'Estudiante' THEN e.email
            ELSE ae.email
        END as email,
        CASE 
            WHEN i.tipo = 'Estudiante' THEN e.celular
            ELSE ae.telefono
        END as telefono,
        CASE 
            WHEN i.tipo = 'Estudiante' THEN e.iglesia
            ELSE ae.congregacion
        END as congregacion
    FROM inscritos i
    LEFT JOIN estudiantes e ON i.id_estudiante = e.id
    LEFT JOIN asistentes_externos ae ON i.id_asistente = ae.id
    WHERE i.fecha_llegada IS NULL
    ORDER BY nombre";

$result = pg_query_params($conexion, $query, [$id_congreso]);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Error al consultar la base de datos']);
    exit;
}

$no_asistentes = [];
while ($row = pg_fetch_assoc($result)) {
    $no_asistentes[] = [
        'nombre' => $row['nombre'],
        'email' => $row['email'],
        'telefono' => $row['telefono'],
        'congregacion' => $row['congregacion'],
        'tipo' => $row['tipo'],
        'numero_boleta' => $row['numero_boleta'],
        'codigo_completo' => $row['codigo_completo'],
        'taller' => $row['taller_asignado']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $no_asistentes,
    'total' => count($no_asistentes)
]);

pg_close($conexion);
exit; 