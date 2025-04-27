<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Cargar el entorno de WordPress
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

try {
    // Consulta para obtener los niveles
    $query = "SELECT id, nombre FROM niveles ORDER BY id ASC";
    $result = pg_query($conexion, $query);

    if (!$result) {
        throw new Exception('Error al obtener los niveles de la base de datos.');
    }

    $niveles = [];
    while ($row = pg_fetch_assoc($result)) {
        $niveles[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre']
        ];
    }

    // Respuesta en formato JSON
    echo json_encode(['success' => true, 'niveles' => $niveles]);
} catch (Exception $e) {
    // Respuesta de error en caso de fallo
    http_response_code(500); // Error interno del servidor
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Cerrar la conexión a la base de datos
pg_close($conexion);
?>