<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    wp_send_json(['error' => 'No autorizado'], 401);
    exit;
}

$response = [];

try {
    // Estudiantes activos (que tienen al menos un curso con porcentaje menor a 100)
    $result = pg_query($conexion, "
        SELECT COUNT(DISTINCT e.id)
        FROM estudiantes e
        INNER JOIN estudiantes_cursos ec ON e.id = ec.estudiante_id
        WHERE ec.porcentaje > 70
    ");
    $estudiantes_activos = pg_fetch_result($result, 0, 0);

    // Cursos este mes
    $mes_actual = date('m');
    $anio_actual = date('Y');
    $result = pg_query_params($conexion, "
        SELECT COUNT(*)
        FROM estudiantes_cursos
        WHERE EXTRACT(MONTH FROM fecha) = $1
        AND EXTRACT(YEAR FROM fecha) = $2
    ", [$mes_actual, $anio_actual]);
    $cursos_mes = pg_fetch_result($result, 0, 0);

    // Total de cursos completados
    $result = pg_query($conexion, "
        SELECT COUNT(*)
        FROM estudiantes_cursos
        WHERE porcentaje > 70
    ");
    $cursos_completados = pg_fetch_result($result, 0, 0);

    // Total de contactos registrados
    $result = pg_query($conexion, "
        SELECT COUNT(*)
        FROM contactos
    ");
    $contactos_activos = pg_fetch_result($result, 0, 0);

    // Actividad reciente (últimas 5 actividades)
    $result = pg_query($conexion, "
        (SELECT 
            'estudiante' as tipo,
            CONCAT('Nuevo estudiante: ', nombre1, ' ', apellido1) as texto,
            fecha_registro as fecha
        FROM estudiantes
        ORDER BY fecha_registro DESC
        LIMIT 2)
        UNION ALL
        (SELECT 
            'curso' as tipo,
            CONCAT('Curso completado: ', c.nombre) as texto,
            ec.fecha as fecha
        FROM estudiantes_cursos ec
        INNER JOIN cursos c ON ec.curso_id = c.id
        WHERE ec.porcentaje > 70
        ORDER BY ec.fecha DESC
        LIMIT 2)
        UNION ALL
        (SELECT 
            'contacto' as tipo,
            CONCAT('Nuevo contacto: ', nombre) as texto,
            fecha_registro as fecha
        FROM contactos
        ORDER BY fecha_registro DESC
        LIMIT 1)
        ORDER BY fecha DESC
        LIMIT 5
    ");

    $actividad_reciente = [];
    while ($row = pg_fetch_object($result)) {
        $fecha = strtotime($row->fecha);
        $ahora = time();
        $diferencia = $ahora - $fecha;
        
        if ($diferencia < 60) {
            $row->tiempo = "Hace unos segundos";
        } elseif ($diferencia < 3600) {
            $minutos = floor($diferencia / 60);
            $row->tiempo = "Hace " . $minutos . " minuto" . ($minutos != 1 ? "s" : "");
        } elseif ($diferencia < 86400) {
            $horas = floor($diferencia / 3600);
            $row->tiempo = "Hace " . $horas . " hora" . ($horas != 1 ? "s" : "");
        } else {
            $dias = floor($diferencia / 86400);
            $row->tiempo = "Hace " . $dias . " día" . ($dias != 1 ? "s" : "");
        }
        
        $actividad_reciente[] = $row;
    }

    wp_send_json([
        'success' => true,
        'data' => [
            'estudiantes_activos' => $estudiantes_activos,
            'cursos_mes' => $cursos_mes,
            'cursos_completados' => $cursos_completados,
            'contactos_activos' => $contactos_activos,
            'actividad_reciente' => $actividad_reciente
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en obtener_estadisticas.php: " . $e->getMessage() . "\n", 3, __DIR__ . "/error.log");
    wp_send_json([
        'success' => false,
        'error' => 'Error al obtener las estadísticas'
    ]);
} 