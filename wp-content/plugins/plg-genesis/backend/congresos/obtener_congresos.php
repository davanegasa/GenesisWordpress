<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Cargar WordPress
require_once(plugin_dir_path(__FILE__) . '/../../backend/db_docker.php'); // Conexión a PostgreSQL para Docker

header('Content-Type: application/json');

// Verificar autenticaci��n en WordPress
if (!is_user_logged_in()) {
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

// **Consulta 1: Obtener informaci��n de congresos con totales de asistentes**
$query_congresos = "
    SELECT 
        c.id AS id_congreso,
        c.nombre AS nombre_congreso,
        c.fecha AS fecha_congreso,
        c.estado,
        COALESCE(COUNT(a.id), 0) AS total_asistentes,
        COALESCE(SUM(CASE WHEN a.id_estudiante IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_estudiantes,
        COALESCE(SUM(CASE WHEN a.id_asistente IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_externos
    FROM congresos c
    LEFT JOIN asistencias_congresos a ON c.id = a.id_congreso
    GROUP BY c.id, c.nombre, c.fecha, c.estado
    ORDER BY c.fecha DESC;
";

$result_congresos = pg_query($conexion, $query_congresos);
if (!$result_congresos) {
    echo json_encode(["success" => false, "error" => "Error en la consulta de congresos: " . pg_last_error($conexion)]);
    exit;
}
$congresos = pg_fetch_all($result_congresos) ?: [];

// **Consulta 2: Obtener cantidad de asistentes por contacto segmentado por congreso**
$query_asistentes_contacto = "
    SELECT 
        ac.id_congreso,
        co.id AS id_contacto,
        co.iglesia AS nombre_contacto,
        COUNT(CASE WHEN ac.id_estudiante IS NOT NULL THEN 1 END) AS estudiantes,
        COUNT(CASE WHEN ac.id_asistente IS NOT NULL THEN 1 END) AS asistentes_externos,
        (SELECT COUNT(*) FROM estudiantes WHERE id_contacto = co.id) AS estudiantes_inscritos
    FROM asistencias_congresos ac
    LEFT JOIN estudiantes e ON ac.id_estudiante = e.id
    LEFT JOIN asistentes_externos ae ON ac.id_asistente = ae.id
    LEFT JOIN contactos co ON (e.id_contacto = co.id OR ae.id_contacto = co.id)
    GROUP BY ac.id_congreso, co.id, co.iglesia
    ORDER BY ac.id_congreso, co.iglesia;
";

$result_asistentes_contacto = pg_query($conexion, $query_asistentes_contacto);
if (!$result_asistentes_contacto) {
    echo json_encode(["success" => false, "error" => "Error en la consulta de asistentes por contacto: " . pg_last_error($conexion)]);
    exit;
}

$asistentes_contacto = pg_fetch_all($result_asistentes_contacto) ?: [];

// **Reestructurar los datos para que los asistentes por contacto queden dentro de su congreso correspondiente**
$congresos_map = [];
foreach ($congresos as $congreso) {
    $congresos_map[$congreso['id_congreso']] = array_merge($congreso, ["detalle_contacto" => []]);

    // Convertir strings num��ricos a enteros
    $congresos_map[$congreso['id_congreso']]['total_asistentes'] = (int) $congreso['total_asistentes'];
    $congresos_map[$congreso['id_congreso']]['total_estudiantes'] = (int) $congreso['total_estudiantes'];
    $congresos_map[$congreso['id_congreso']]['total_externos'] = (int) $congreso['total_externos'];
}

// Asignar los contactos a cada congreso
foreach ($asistentes_contacto as $contacto) {
    $id_congreso = $contacto['id_congreso'];
    if (isset($congresos_map[$id_congreso])) {
        $congresos_map[$id_congreso]['detalle_contacto'][] = [
            "id_contacto" => (int) $contacto['id_contacto'],
            "nombre_contacto" => $contacto['nombre_contacto'],
            "estudiantes" => (int) $contacto['estudiantes'],
            "asistentes_externos" => (int) $contacto['asistentes_externos'],
            "estudiantes_inscritos" => (int) $contacto['estudiantes_inscritos']
        ];
    }
}

// **Responder con JSON**
$response = [
    "success" => true,
    "congresos" => array_values($congresos_map)
];

echo json_encode($response);
?>