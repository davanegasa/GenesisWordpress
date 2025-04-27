<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Validar que el método sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Validar que se pase el parámetro contacto_id
if (!isset($_GET['contacto_id']) || empty($_GET['contacto_id'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['success' => false, 'error' => 'El ID del contacto es requerido']);
    exit;
}

$contactoId = intval($_GET['contacto_id']);

// Realizar consulta a la base de datos para obtener estudiantes
$query = "
    SELECT 
        e.id_estudiante,
        CONCAT(e.nombre1, ' ', e.nombre2, ' ', e.apellido1, ' ', e.apellido2) AS nombre_completo,
        e.doc_identidad,
        e.celular,
        e.email
    FROM estudiantes e
    WHERE e.id_contacto = $1
";
$result = pg_query_params($conexion, $query, [$contactoId]);

if (!$result) {
    http_response_code(500); // Error interno del servidor
    echo json_encode(['success' => false, 'error' => 'Error al obtener los datos de los estudiantes']);
    exit;
}

// Construir el resultado
$estudiantes = [];
while ($row = pg_fetch_assoc($result)) {
    $estudiantes[] = [
        'id_estudiante' => $row['id_estudiante'],
        'nombre_completo' => $row['nombre_completo'],
        'doc_identidad' => $row['doc_identidad'],
        'celular' => $row['celular'],
        'email' => $row['email'],
    ];
}

// Cerrar conexión a la base de datos
pg_free_result($result);
pg_close($conexion);

// Responder con la lista de estudiantes
echo json_encode(['success' => true, 'estudiantes' => $estudiantes]);
?>