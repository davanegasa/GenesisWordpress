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
if (!isset($data['numero_boleta'], $data['codigo_verificacion'], $data['tipo_registro'], $data['id_congreso'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];
$tipo_registro = $data['tipo_registro']; // 'llegada' o 'almuerzo'
$id_congreso = $data['id_congreso'];

// Buscar la boleta y obtener datos relevantes
$query = "SELECT bc.id, bc.estado, bc.fecha_llegada, bc.fecha_almuerzo, bc.id_asistencia, ac.id_estudiante, ac.id_asistente
          FROM boletas_congresos bc
          JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
          WHERE bc.numero_boleta = $1 AND bc.codigo_verificacion = $2 AND bc.id_congreso = $3";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion, $id_congreso]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Boleta no encontrada o no válida para este congreso']);
    exit;
}

$boleta = pg_fetch_assoc($result);

// Verificar que la boleta esté activa (estado = 'usado')
if ($boleta['estado'] !== 'usado') {
    echo json_encode(['success' => false, 'error' => 'Boleta no activa para este congreso']);
    exit;
}

// Determinar si ya fue registrada la asistencia de este tipo
$campo_fecha = ($tipo_registro === 'llegada') ? 'fecha_llegada' : 'fecha_almuerzo';
if (!empty($boleta[$campo_fecha])) {
    // Obtener datos del participante
    if ($boleta['id_estudiante']) {
        $query = "SELECT nombre1 || ' ' || nombre2 || ' ' || apellido1 || ' ' || apellido2 AS nombre, email, celular, 'Estudiante' AS tipo FROM estudiantes WHERE id = $1";
        $res = pg_query_params($conexion, $query, [$boleta['id_estudiante']]);
        $datos = pg_fetch_assoc($res);
    } else if ($boleta['id_asistente']) {
        $query = "SELECT nombre, email, telefono AS celular, 'Externo' AS tipo FROM asistentes_externos WHERE id = $1";
        $res = pg_query_params($conexion, $query, [$boleta['id_asistente']]);
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
    $query = "SELECT nombre1 || ' ' || nombre2 || ' ' || apellido1 || ' ' || apellido2 AS nombre, email, celular, 'Estudiante' AS tipo FROM estudiantes WHERE id = $1";
    $res = pg_query_params($conexion, $query, [$boleta['id_estudiante']]);
    $datos = pg_fetch_assoc($res);
} else if ($boleta['id_asistente']) {
    $query = "SELECT nombre, email, telefono AS celular, 'Externo' AS tipo FROM asistentes_externos WHERE id = $1";
    $res = pg_query_params($conexion, $query, [$boleta['id_asistente']]);
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