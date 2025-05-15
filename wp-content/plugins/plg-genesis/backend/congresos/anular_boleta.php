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
if (!isset($data['numero_boleta'], $data['codigo_verificacion'], $data['id_congreso'], $data['boleta_reemplazo'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$numero_boleta = $data['numero_boleta'];
$codigo_verificacion = $data['codigo_verificacion'];
$id_congreso = $data['id_congreso'];
$boleta_reemplazo = $data['boleta_reemplazo'];

// Iniciar transacción
pg_query($conexion, 'BEGIN');

try {
    // Buscar la boleta a anular
    $query = "SELECT bc.id, bc.id_asistencia, bc.estado, bc.fecha_llegada, bc.fecha_almuerzo
              FROM boletas_congresos bc
              WHERE bc.numero_boleta = $1 AND bc.codigo_verificacion = $2 AND bc.id_congreso = $3";
    $result = pg_query_params($conexion, $query, [$numero_boleta, $codigo_verificacion, $id_congreso]);

    if (!$result || pg_num_rows($result) === 0) {
        throw new Exception('Boleta no encontrada');
    }

    $boleta = pg_fetch_assoc($result);

    // Verificar que la boleta esté en estado 'usado'
    if ($boleta['estado'] !== 'usado') {
        throw new Exception('La boleta no está en uso');
    }

    // Buscar la boleta de reemplazo
    $query = "SELECT id, codigo_verificacion, estado 
              FROM boletas_congresos 
              WHERE numero_boleta = $1 AND id_congreso = $2";
    $result = pg_query_params($conexion, $query, [$boleta_reemplazo, $id_congreso]);

    if (!$result || pg_num_rows($result) === 0) {
        throw new Exception('Boleta de reemplazo no encontrada');
    }

    $boleta_reemplazo_data = pg_fetch_assoc($result);

    // Verificar que la boleta de reemplazo esté activa
    if ($boleta_reemplazo_data['estado'] !== 'activo') {
        throw new Exception('La boleta de reemplazo no está activa');
    }

    // Actualizar la boleta de reemplazo con los datos de la boleta anulada
    $query = "UPDATE boletas_congresos 
              SET id_asistencia = $1, 
                  estado = 'usado',
                  fecha_llegada = $2,
                  fecha_almuerzo = $3
              WHERE id = $4";
    pg_query_params($conexion, $query, [
        $boleta['id_asistencia'],
        $boleta['fecha_llegada'],
        $boleta['fecha_almuerzo'],
        $boleta_reemplazo_data['id']
    ]);

    // Anular la boleta original
    $query = "UPDATE boletas_congresos SET estado = 'anulado' WHERE id = $1";
    pg_query_params($conexion, $query, [$boleta['id']]);

    // Confirmar transacción
    pg_query($conexion, 'COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Boleta anulada y reemplazada exitosamente',
        'nueva_boleta' => [
            'numero' => $boleta_reemplazo,
            'codigo' => $boleta_reemplazo_data['codigo_verificacion']
        ]
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    pg_query($conexion, 'ROLLBACK');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

pg_close($conexion);
exit; 