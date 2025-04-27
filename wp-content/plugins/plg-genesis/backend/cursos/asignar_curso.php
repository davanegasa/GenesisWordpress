<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validar entrada
if (!isset($data['estudiante_id'], $data['curso_id'], $data['porcentaje'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$estudiante_id = intval($data['estudiante_id']);
$curso_id = intval($data['curso_id']);
$porcentaje = floatval($data['porcentaje']);

if ($porcentaje < 0 || $porcentaje > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Porcentaje debe estar entre 0 y 100']);
    exit;
}

// Insertar en la tabla estudiantes_cursos
$query = "
    INSERT INTO estudiantes_cursos (estudiante_id, curso_id, porcentaje, fecha)
    VALUES ($1, $2, $3, NOW())
";

$result = pg_query_params($conexion, $query, [$estudiante_id, $curso_id, $porcentaje]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al asignar el curso']);
}

pg_close($conexion);
?>