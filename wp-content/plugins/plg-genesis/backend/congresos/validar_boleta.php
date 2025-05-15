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
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida, se esperaba JSON']);
    exit;
}

// Leer el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['numero_boleta'], $data['codigo_verificacion'], $data['id_congreso'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];
$id_congreso = $data['id_congreso'];

// Validar la boleta
$query = "SELECT bc.id, bc.id_congreso, bc.estado, bc.id_asistencia, ac.id_estudiante, ac.id_asistente 
          FROM boletas_congresos bc
          JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
          WHERE bc.numero_boleta = $1 AND bc.codigo_verificacion = $2 AND bc.id_congreso = $3";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion, $id_congreso]);

$response = ['success' => false];

if ($result && pg_num_rows($result) > 0) {
    $boleta = pg_fetch_assoc($result);
    
    // Verificar si el usuario ya está registrado
    $response = [
        'success' => true,
        'id_congreso' => $boleta['id_congreso'],
        'estado' => $boleta['estado'],
        'registrado' => false,
    ];

    if (!empty($boleta['id_estudiante'])) {
        $query = "SELECT nombre1 || ' ' || nombre2 || ' ' || apellido1 || ' ' || apellido2 as nombre, 
                         email, celular, 'Estudiante' as tipo 
                  FROM estudiantes WHERE id = $1";
        $res = pg_query_params($conexion, $query, [$boleta['id_estudiante']]);
        if ($res && pg_num_rows($res) > 0) {
            $response['registrado'] = true;
            $response['tipo'] = 'estudiante';
            $response['datos'] = pg_fetch_assoc($res);
        }
    } elseif (!empty($boleta['id_asistente'])) {
        $query = "SELECT nombre, email, telefono as celular, 'Externo' as tipo 
                  FROM asistentes_externos WHERE id = $1";
        $res = pg_query_params($conexion, $query, [$boleta['id_asistente']]);
        if ($res && pg_num_rows($res) > 0) {
            $response['registrado'] = true;
            $response['tipo'] = 'asistente_externo';
            $response['datos'] = pg_fetch_assoc($res);
        }
    }
} else {
    $response['error'] = 'Boleta inválida o no corresponde a este congreso';
}

pg_close($conexion);

// Devolver siempre JSON
echo json_encode($response);
exit;
