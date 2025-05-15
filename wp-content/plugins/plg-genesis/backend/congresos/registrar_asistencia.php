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

// Log para debug
error_log('Datos recibidos: ' . print_r($data, true));

// Validar datos obligatorios
if (!isset($data['numero_boleta'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Procesar código de barras de 7 dígitos
$codigo_barras = $data['numero_boleta'];
if (strlen($codigo_barras) == 7) {
    $numero_boleta = substr($codigo_barras, 0, 3);  // Primeros 3 dígitos
    $codigo_verificacion = substr($codigo_barras, 3); // Últimos 4 dígitos
} else {
    $numero_boleta = $data['numero_boleta'];
    $codigo_verificacion = isset($data['codigo_verificacion']) ? $data['codigo_verificacion'] : '';
}

error_log('Procesando boleta - Número: ' . $numero_boleta . ', Código: ' . $codigo_verificacion);

$tipo_registro = $data['tipo_registro']; // 'llegada' o 'almuerzo'
$id_congreso = isset($data['id_congreso']) ? intval($data['id_congreso']) : 4; // Usar 4 como valor por defecto

// Log para debug
error_log('ID del congreso procesado: ' . $id_congreso);

// Validar que el ID del congreso sea válido
if ($id_congreso <= 0) {
    error_log('ID del congreso inválido: ' . $id_congreso);
    echo json_encode(['success' => false, 'error' => 'ID de congreso no válido']);
    exit;
}

// Verificar si el congreso existe
$query_congreso = "SELECT id FROM congresos WHERE id = $1";
$result_congreso = pg_query_params($conexion, $query_congreso, [$id_congreso]);

if (!$result_congreso || pg_num_rows($result_congreso) === 0) {
    error_log('Congreso no encontrado en la base de datos: ' . $id_congreso);
    echo json_encode(['success' => false, 'error' => 'Congreso no encontrado']);
    exit;
}

// Buscar la boleta y obtener datos relevantes
if ($numero_boleta === '518') {
    error_log('Debug - Consultando boleta 518');
    $debug_query = "
        SELECT 
            bc.id as boleta_id,
            bc.estado,
            bc.numero_boleta,
            bc.codigo_verificacion,
            bc.id_asistencia,
            ac.id as asistencia_id,
            ac.id_estudiante,
            ac.id_asistente,
            ac.taller_asignado,
            CASE 
                WHEN ac.id_estudiante IS NOT NULL THEN (
                    SELECT nombre1 || ' ' || nombre2 || ' ' || apellido1 || ' ' || apellido2 
                    FROM estudiantes 
                    WHERE id = ac.id_estudiante
                )
                ELSE (
                    SELECT nombre 
                    FROM asistentes_externos 
                    WHERE id = ac.id_asistente
                )
            END as nombre_completo
        FROM boletas_congresos bc
        JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
        WHERE bc.numero_boleta = $1 
        AND bc.codigo_verificacion = $2 
        AND bc.id_congreso = $3";
    $debug_result = pg_query_params($conexion, $debug_query, [$numero_boleta, $codigo_verificacion, $id_congreso]);
    if ($debug_result) {
        error_log('Debug - Datos completos de la boleta 518: ' . print_r(pg_fetch_assoc($debug_result), true));
    }
}

$query = "SELECT bc.id, bc.estado, bc.fecha_llegada, bc.fecha_almuerzo, bc.id_asistencia, ac.id_estudiante, ac.id_asistente, ac.taller_asignado
          FROM boletas_congresos bc
          JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
          WHERE bc.numero_boleta = $1 AND bc.codigo_verificacion = $2 AND bc.id_congreso = $3";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion, $id_congreso]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Boleta no encontrada o no válida para este congreso']);
    exit;
}

$boleta = pg_fetch_assoc($result);
if (!$boleta) {
    echo json_encode(['success' => false, 'error' => 'Error al leer los datos de la boleta']);
    exit;
}

// Verificar que la boleta esté activa (estado = 'usado')
if ($boleta['estado'] !== 'usado') {
    echo json_encode(['success' => false, 'error' => 'Boleta no activa para este congreso']);
    exit;
}

// Validación específica para almuerzo
if ($tipo_registro === 'almuerzo') {
    if ($boleta['fecha_llegada'] === null) {
        echo json_encode(['success' => false, 'error' => 'Boleta sin llegada al congreso']);
        exit;
    }
    if ($boleta['fecha_almuerzo'] !== null) {
        echo json_encode(['success' => false, 'error' => 'Almuerzo ya entregado']);
        exit;
    }
}

// Determinar si ya fue registrada la asistencia de este tipo
$campo_fecha = ($tipo_registro === 'llegada') ? 'fecha_llegada' : 'fecha_almuerzo';
if (!empty($boleta[$campo_fecha])) {
    // Obtener datos del participante
    if ($boleta['id_estudiante']) {
        error_log('Debug - id_estudiante: ' . $boleta['id_estudiante'] . ', id_asistencia: ' . $boleta['id_asistencia']);
        
        $query = "SELECT 
            e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 AS nombre, 
            e.email, 
            e.celular, 
            'Estudiante' AS tipo,
            (SELECT taller_asignado FROM asistencias_congresos WHERE id = $2) as taller,
            $2 as debug_id_asistencia
        FROM estudiantes e
        WHERE e.id = $1";
        
        $res = pg_query_params($conexion, $query, [$boleta['id_estudiante'], $boleta['id_asistencia']]);
        $datos = pg_fetch_assoc($res);
        error_log('Debug - Query estudiante: ' . $query);
        error_log('Debug - Params: id_estudiante=' . $boleta['id_estudiante'] . ', id_asistencia=' . $boleta['id_asistencia']);
        error_log('Debug - Datos estudiante: ' . print_r($datos, true));
    } else if ($boleta['id_asistente']) {
        $query = "SELECT 
            ae.nombre, 
            ae.email, 
            ae.telefono AS celular, 
            'Externo' AS tipo,
            (SELECT taller_asignado FROM asistencias_congresos WHERE id = $2) as taller
        FROM asistentes_externos ae
        WHERE ae.id = $1";
        $res = pg_query_params($conexion, $query, [$boleta['id_asistente'], $boleta['id_asistencia']]);
        $datos = pg_fetch_assoc($res);
    } else {
        $datos = null;
    }
    echo json_encode([
        'success' => false,
        'error' => 'La asistencia de ' . ($tipo_registro === 'llegada' ? 'llegada' : 'almuerzo') . ' ya fue registrada el ' . $boleta[$campo_fecha],
        'fecha_registro' => $boleta[$campo_fecha],
        'participante' => $datos
    ]);
    exit;
}

// Registrar la asistencia
$query = "UPDATE boletas_congresos SET $campo_fecha = CURRENT_TIMESTAMP WHERE id = $1 RETURNING $campo_fecha";
$result = pg_query_params($conexion, $query, [$boleta['id']]);
if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Error al registrar la asistencia']);
    exit;
}
$updated = pg_fetch_assoc($result);

// Obtener datos del participante
if ($boleta['id_estudiante']) {
    error_log('Debug - id_estudiante: ' . $boleta['id_estudiante'] . ', id_asistencia: ' . $boleta['id_asistencia']);
    
    $query = "SELECT 
        e.nombre1 || ' ' || e.nombre2 || ' ' || e.apellido1 || ' ' || e.apellido2 AS nombre, 
        e.email, 
        e.celular, 
        'Estudiante' AS tipo,
        (SELECT taller_asignado FROM asistencias_congresos WHERE id = $2) as taller,
        $2 as debug_id_asistencia
    FROM estudiantes e
    WHERE e.id = $1";
    
    $res = pg_query_params($conexion, $query, [$boleta['id_estudiante'], $boleta['id_asistencia']]);
    $datos = pg_fetch_assoc($res);
    error_log('Debug - Query estudiante: ' . $query);
    error_log('Debug - Params: id_estudiante=' . $boleta['id_estudiante'] . ', id_asistencia=' . $boleta['id_asistencia']);
    error_log('Debug - Datos estudiante: ' . print_r($datos, true));
} else if ($boleta['id_asistente']) {
    $query = "SELECT 
        ae.nombre, 
        ae.email, 
        ae.telefono AS celular, 
        'Externo' AS tipo,
        (SELECT taller_asignado FROM asistencias_congresos WHERE id = $2) as taller
    FROM asistentes_externos ae
    WHERE ae.id = $1";
    $res = pg_query_params($conexion, $query, [$boleta['id_asistente'], $boleta['id_asistencia']]);
    $datos = pg_fetch_assoc($res);
} else {
    $datos = null;
}

echo json_encode([
    'success' => true,
    'message' => 'Asistencia registrada correctamente',
    'fecha_registro' => $updated[$campo_fecha],
    'participante' => $datos
]);
pg_close($conexion);
exit; 