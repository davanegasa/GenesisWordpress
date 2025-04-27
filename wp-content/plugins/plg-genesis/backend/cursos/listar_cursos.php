<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$query = "
    SELECT niveles.id AS nivel_id, niveles.nombre AS nivel_nombre, cursos.id AS curso_id, cursos.nombre AS curso_nombre, cursos.descripcion
    FROM cursos
    INNER JOIN niveles ON cursos.nivel_id = niveles.id
    ORDER BY niveles.id, cursos.id
";
$result = pg_query($conexion, $query);

if (!$result) {
    http_response_code(500); // Error del servidor
    echo json_encode(['error' => 'Error al obtener los cursos']);
    exit;
}

$cursos = [];
while ($row = pg_fetch_assoc($result)) {
    // Si el nivel aún no existe en el array, lo inicializamos con su ID y nombre
    if (!isset($cursos[$row['nivel_id']])) {
        $cursos[$row['nivel_id']] = [
            'nombre' => $row['nivel_nombre'],
            'cursos' => []
        ];
    }

    // Agregamos el curso al nivel correspondiente
    $cursos[$row['nivel_id']]['cursos'][] = [
        'id' => $row['curso_id'],
        'nombre' => $row['curso_nombre'],
        'descripcion' => $row['descripcion']
    ];
}

echo json_encode(['success' => true, 'niveles' => $cursos]);

pg_close($conexion);
?>