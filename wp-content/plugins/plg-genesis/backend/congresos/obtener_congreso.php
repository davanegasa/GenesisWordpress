<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Cargar WordPress
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php'); // Conexión a PostgreSQL

// Verificar autenticación del usuario en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

global $conexion;
header('Content-Type: application/json');
$input = json_decode(file_get_contents("php://input"), true);

$nombre = $input['nombre'] ?? '';

// Consulta optimizada para traer congresos con estadísticas
$query = "
    SELECT c.id, c.nombre, c.fecha,
           COALESCE(COUNT(a.id), 0) AS total_asistentes,
           COALESCE(SUM(CASE WHEN a.id_estudiante IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_estudiantes,
           COALESCE(SUM(CASE WHEN a.id_asistente IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_externos,
           COALESCE(SUM(CASE WHEN a.id_asistente IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_externos,
           (SELECT json_agg(json_build_object('id_contacto', e.id_contacto, 'cantidad', COUNT(*)))
            FROM estudiantes e
            JOIN asistencias_congresos ac ON e.id = ac.id_estudiante
            WHERE ac.id_congreso = c.id
            GROUP BY e.id_contacto) AS estudiantes_por_contacto
    FROM congresos c
    LEFT JOIN asistencias_congresos a ON c.id = a.id_congreso
    WHERE c.nombre ILIKE $1
    GROUP BY c.id;
";

$result = pg_query_params($conexion, $query, ['%' . $nombre . '%']);

if (!$result) {
    echo json_encode(["success" => false, "error" => "Error en la consulta: " . pg_last_error($conexion)]);
    exit;
}

$congresos = pg_fetch_all($result);

// Respuesta en formato JSON
echo json_encode($congresos ?: []);
?>
