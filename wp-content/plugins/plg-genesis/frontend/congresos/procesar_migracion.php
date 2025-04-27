<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Cargar WordPress
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php'); // Conexión a PostgreSQL

global $wpdb;

// Verificar autenticación del usuario en WordPress
if (!is_user_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Validar que la solicitud sea POST con JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida, se esperaba JSON']);
    exit;
}

// Leer el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_congreso'], $data['asistentes'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_congreso = $data['id_congreso'];
$asistentes = $data['asistentes'];

$success = [];
$errors = [];

foreach ($asistentes as $asistente) {
    $identificacion = $asistente['identificacion'];
    $idSeleccionado = $asistente['idSeleccionado'];

    if ($idSeleccionado !== 'nuevo') {
        // Registrar asistencia del estudiante existente
        $query = "INSERT INTO asistencias_congresos (id_estudiante, id_congreso, asistencia) VALUES ($1, $2, true)";
        $result = pg_query_params($conexion, $query, [$idSeleccionado, $id_congreso]);
    } else {
        // Buscar si el asistente ya existe en asistentes_externos
        $query = "SELECT id FROM asistentes_externos WHERE identificacion = $1";
        $result = pg_query_params($conexion, $query, [$identificacion]);

        if ($result && pg_num_rows($result) > 0) {
            // ✅ Si el asistente ya existe, usar el ID existente
            $row = pg_fetch_assoc($result);
            $id_asistente = $row['id'];
        } else {
            // ❌ Si no existe, crearlo en asistentes_externos
            global $wpdb;
            $asistente_wp = $wpdb->get_row($wpdb->prepare(
                "SELECT nombre, ident AS identificacion, telef AS telefono, email, congreg AS congregacion 
                FROM asistentes 
                WHERE ident = %s", $identificacion));

            if (!$asistente_wp) {
                $errors[] = "No se encontró el asistente con identificación: $identificacion";
                continue;
            }

            $query = "INSERT INTO asistentes_externos (id_contacto, nombre, identificacion, telefono, email, congregacion) 
                      VALUES (108, $1, $2, $3, $4, $5) RETURNING id";
            $result = pg_query_params($conexion, $query, [
                $asistente_wp->nombre,
                $asistente_wp->identificacion,
                $asistente_wp->telefono,
                $asistente_wp->email,
                $asistente_wp->congregacion
            ]);

            if ($result) {
                $row = pg_fetch_assoc($result);
                $id_asistente = $row['id'];
            } else {
                $errors[] = "Error al insertar nuevo asistente con identificación: $identificacion";
                continue;
            }
        }

        // ✅ Registrar asistencia del asistente en el nuevo congreso
        $query = "INSERT INTO asistencias_congresos (id_asistente, id_congreso, asistencia) VALUES ($1, $2, true)";
        $result = pg_query_params($conexion, $query, [$id_asistente, $id_congreso]);
    }

    if ($result) {
        // ✅ Actualizar el campo `migracion` en la base de datos de WordPress
        $wpdb->update(
            'asistentes',
            ['migracion' => 1],
            ['ident' => $identificacion],
            ['%d'],
            ['%s']
        );
        
        $success[] = "Migración actualizada para identificación: $identificacion";
    } else {
        $errors[] = "Error al registrar asistencia para identificación: $identificacion";
    }
}

pg_close($conexion);

// Responder con JSON
echo json_encode(['success' => $success, 'errors' => $errors]);
exit;
