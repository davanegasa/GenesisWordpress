<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener el día, mes y año de la URL
$dia = isset($_GET['dia']) ? intval($_GET['dia']) : intval(date('d'));
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Formatear la fecha para la consulta
$fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

// Obtener los cursos del día
$query = "SELECT 
    ec.id as estudiante_curso_id,
    COALESCE(e.id_estudiante, e.id::text) as estudiante_id,
    c.descripcion as nombre_curso,
    CONCAT(e.nombre1, ' ', COALESCE(e.nombre2, ''), ' ', e.apellido1, ' ', COALESCE(e.apellido2, '')) as nombre_estudiante,
    e.celular,
    cont.nombre as nombre_contacto,
    ec.porcentaje as nota
FROM estudiantes_cursos ec
JOIN cursos c ON ec.curso_id = c.id
JOIN estudiantes e ON ec.estudiante_id = e.id
LEFT JOIN contactos cont ON e.id_contacto = cont.id
WHERE DATE(ec.fecha) = '$fecha'
ORDER BY ec.fecha ASC";

$resultado = pg_query($conexion, $query);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los cursos: ' . pg_last_error($conexion)]);
    exit;
}

$cursos = [];
while ($row = pg_fetch_assoc($resultado)) {
    $cursos[] = $row;
}

// Preparar la respuesta
$respuesta = [
    'success' => true,
    'fecha' => $fecha,
    'cursos' => $cursos
];

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
pg_close($conexion); 