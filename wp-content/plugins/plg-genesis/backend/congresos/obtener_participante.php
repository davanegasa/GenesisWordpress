<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['numero_boleta'], $data['codigo_verificacion'], $data['id_congreso'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];
$id_congreso = $data['id_congreso'];

// Buscar la boleta y obtener el id_asistencia
$query = "SELECT bc.id, bc.id_asistencia, ac.id_estudiante, ac.id_asistente
          FROM boletas_congresos bc
          JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
          WHERE bc.numero_boleta = $1 AND bc.codigo_verificacion = $2 AND bc.id_congreso = $3";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion, $id_congreso]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Boleta no encontrada o no válida para este congreso']);
    exit;
}

$boleta = pg_fetch_assoc($result);

// Determinar si es estudiante o externo y obtener los datos
if ($boleta['id_estudiante']) {
    $query = "SELECT nombre1 || ' ' || nombre2 || ' ' || apellido1 || ' ' || apellido2 AS nombre, email, celular, 'Estudiante' AS tipo
              FROM estudiantes WHERE id = $1";
    $res = pg_query_params($conexion, $query, [$boleta['id_estudiante']]);
    $datos = pg_fetch_assoc($res);
} else if ($boleta['id_asistente']) {
    $query = "SELECT nombre, email, telefono AS celular, 'Externo' AS tipo FROM asistentes_externos WHERE id = $1";
    $res = pg_query_params($conexion, $query, [$boleta['id_asistente']]);
    $datos = pg_fetch_assoc($res);
} else {
    echo json_encode(['success' => false, 'error' => 'No se encontró información del participante']);
    exit;
}

if (!$datos) {
    echo json_encode(['success' => false, 'error' => 'No se encontró información del participante']);
    exit;
}

echo json_encode(['success' => true, 'data' => $datos]);
pg_close($conexion);
exit; 