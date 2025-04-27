<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar si es una solicitud GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Consulta para obtener los cursos
$query = "
    SELECT 
        cursos.id AS curso_id,
        cursos.nombre AS curso_nombre,
        niveles.nombre AS nivel_nombre,
        cursos.descripcion AS curso_descripcion
    FROM cursos
    INNER JOIN niveles ON cursos.nivel_id = niveles.id
    ORDER BY niveles.id, cursos.id
";

$result = pg_query($conexion, $query);

if (!$result) {
    http_response_code(500); // Error en el servidor
    echo json_encode(['error' => 'Error al obtener los cursos']);
    exit;
}

// Formatear los datos en un array estructurado
$cursos = [];
while ($row = pg_fetch_assoc($result)) {
    $cursos[] = [
        'id' => $row['curso_id'],
        'nombre' => $row['curso_nombre'],
        'nivel' => $row['nivel_nombre'],
        'descripcion' => $row['curso_descripcion']
    ];
}

// Enviar la respuesta como JSON
echo json_encode(['success' => true, 'cursos' => $cursos]);

// Cerrar la conexión a la base de datos
pg_close($conexion);
?>