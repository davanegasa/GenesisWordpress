<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../db.php');

// Obtener el mes y año de la solicitud
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Consulta para obtener la cantidad de cursos por día
$query_cursos = "
    SELECT DATE(fecha) as dia, COUNT(*) as cantidad
    FROM estudiantes_cursos
    WHERE EXTRACT(MONTH FROM fecha) = $mes
    AND EXTRACT(YEAR FROM fecha) = $anio
    GROUP BY DATE(fecha)
    ORDER BY dia";
$resultado_cursos = pg_query($conexion, $query_cursos);

// Crear un array asociativo con la cantidad de cursos por día
$cursos_por_dia = [];
while ($row = pg_fetch_assoc($resultado_cursos)) {
    $dia = date('j', strtotime($row['dia']));
    $cursos_por_dia[$dia] = intval($row['cantidad']);
}

// Consulta para obtener el total de cursos del mes
$query_total = "
    SELECT COUNT(*) as total
    FROM estudiantes_cursos
    WHERE EXTRACT(MONTH FROM fecha) = $mes
    AND EXTRACT(YEAR FROM fecha) = $anio";
$resultado_total = pg_query($conexion, $query_total);
$total_mes = pg_fetch_assoc($resultado_total)['total'];

// Preparar la respuesta
$respuesta = [
    'cursos_por_dia' => $cursos_por_dia,
    'total_mes' => intval($total_mes),
    'mes' => $mes,
    'anio' => $anio
];

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($respuesta); 