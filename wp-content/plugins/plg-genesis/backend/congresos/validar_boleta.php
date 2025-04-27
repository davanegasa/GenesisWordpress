<?php
require_once(__DIR__ . '/../../backend/db_public.php'); // Conexión pública segura

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

if (!$data || !isset($data['numero_boleta'], $data['codigo_verificacion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];

// Validar la boleta en la base de datos pública
$query = "SELECT id_congreso, estado, id_estudiante, id_asistente FROM boletas_congresos WHERE numero_boleta = $1 AND codigo_verificacion = $2";
$result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion]);

$response = ['success' => false];

if ($result && pg_num_rows($result) > 0) {
    $boleta = pg_fetch_assoc($result);
    if ($boleta['estado'] === 'usado') {
        $response['error'] = 'Esta boleta ya ha sido usada';
    } else {
        // Verificar si el usuario ya está registrado
        $response = [
            'success' => true,
            'id_congreso' => $boleta['id_congreso'],
            'estado' => $boleta['estado'],
            'registrado' => false,
        ];

        if (!empty($boleta['id_estudiante'])) {
            $query = "SELECT * FROM estudiantes WHERE id = $1";
            $res = pg_query_params($conexion, $query, [$boleta['id_estudiante']]);
            if ($res && pg_num_rows($res) > 0) {
                $response['registrado'] = true;
                $response['tipo'] = 'estudiante';
                $response['datos'] = pg_fetch_assoc($res);
            }
        } elseif (!empty($boleta['id_asistente'])) {
            $query = "SELECT * FROM asistentes_externos WHERE id = $1";
            $res = pg_query_params($conexion, $query, [$boleta['id_asistente']]);
            if ($res && pg_num_rows($res) > 0) {
                $response['registrado'] = true;
                $response['tipo'] = 'asistente_externo';
                $response['datos'] = pg_fetch_assoc($res);
            }
        }
    }
} else {
    $response['error'] = 'Boleta inválida';
}

pg_close($conexion);

// Devolver siempre JSON
echo json_encode($response);
exit;
