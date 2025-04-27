<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de contacto no proporcionado']);
    exit;
}

$contactoId = intval($_GET['id']);

// Consultar las observaciones del contacto
$query = "
    SELECT id, observacion, fecha, tipo, usuario_nombre as usuario
    FROM observaciones_contactos
    WHERE contacto_id = $1
    ORDER BY fecha DESC
";

$result = pg_query_params($conexion, $query, [$contactoId]);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener las observaciones']);
    exit;
}

$observaciones = [];
while ($row = pg_fetch_assoc($result)) {
    $observaciones[] = [
        'id' => $row['id'],
        'observacion' => $row['observacion'],
        'fecha' => $row['fecha'],
        'tipo' => $row['tipo'],
        'usuario' => $row['usuario']
    ];
}

echo json_encode(['success' => true, 'observaciones' => $observaciones]);

pg_close($conexion);