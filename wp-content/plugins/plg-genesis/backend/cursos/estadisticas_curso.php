<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$whereClause = "";
if (isset($_GET['curso_id']) && is_numeric($_GET['curso_id'])) {
    $curso_id = intval($_GET['curso_id']);
    $whereClause = "WHERE c.id = $curso_id";
}

$query = "
    SELECT 
        c.id AS curso_id,
        c.nombre AS curso_nombre, 
        c.descripcion AS curso_descripcion, 
        n.nombre AS nivel, 
        COUNT(ec.id) AS total_inscritos, 
        COALESCE(AVG(ec.porcentaje), 0) AS promedio_calificacion, 
        COUNT(CASE WHEN ec.porcentaje > 70 THEN 1 END) AS total_finalizados_exitosamente,
        (COUNT(CASE WHEN ec.porcentaje > 70 THEN 1 END) * 100.0 / NULLIF(COUNT(ec.id), 0)) AS porcentaje_finalizacion_exitosa,
        MAX(ec.fecha) AS ultima_fecha_registro
    FROM cursos c
    INNER JOIN niveles n ON c.nivel_id = n.id
    LEFT JOIN estudiantes_cursos ec ON c.id = ec.curso_id
    $whereClause
    GROUP BY c.id, n.nombre;
";

$result = pg_query($conexion, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los cursos']);
    exit;
}

$cursos = [];
while ($row = pg_fetch_assoc($result)) {
    $cursos[] = $row;
}

$response = [
    'success' => true,
    'cursos' => $cursos
];

echo json_encode($response);
pg_close($conexion);
?>
