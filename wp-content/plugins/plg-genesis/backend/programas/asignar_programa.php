<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar m¨¦todo HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'M¨¦todo no permitido']);
    exit;
}

// Leer el cuerpo de la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar datos requeridos
if (!isset($data['programa_ids']) || !is_array($data['programa_ids']) || empty($data['programa_ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El arreglo de IDs de programas es requerido y debe contener al menos un elemento.']);
    exit;
}

$programaIds = $data['programa_ids'];
$estudianteId = $data['estudiante_id'] ?? null;
$contactoId = $data['contacto_id'] ?? null;

// Validar que se proporcione estudiante_id o contacto_id (pero no ambos)
if ((!$estudianteId && !$contactoId) || ($estudianteId && $contactoId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Debe proporcionar un estudiante o un contacto, pero no ambos.']);
    exit;
}

// Iniciar transacci¨®n
pg_query($conexion, 'BEGIN');

try {
    foreach ($programaIds as $programaId) {
        $programaId = intval($programaId);

        if ($contactoId) {
            // Asignar el programa al contacto
            $queryAsignar = "
                INSERT INTO programas_asignaciones (programa_id, contacto_id)
                VALUES ($1, $2)
            ";
            $resultAsignar = pg_query_params($conexion, $queryAsignar, [$programaId, $contactoId]);

            if (!$resultAsignar) {
                throw new Exception('Error al asignar el programa al contacto.');
            }
        } elseif ($estudianteId) {
            // Asignar el programa al estudiante
            $queryAsignar = "
                INSERT INTO programas_asignaciones (programa_id, estudiante_id)
                VALUES ($1, $2)
            ";
            $resultAsignar = pg_query_params($conexion, $queryAsignar, [$programaId, $estudianteId]);

            if (!$resultAsignar) {
                throw new Exception('Error al asignar el programa al estudiante.');
            }
        }
    }

    // Finalizar transacci¨®n
    pg_query($conexion, 'COMMIT');
    pg_close($conexion);

    echo json_encode(['success' => true, 'message' => 'Programas asignados exitosamente.']);
} catch (Exception $e) {
    // Revertir transacci¨®n en caso de error
    pg_query($conexion, 'ROLLBACK');
    pg_close($conexion);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}