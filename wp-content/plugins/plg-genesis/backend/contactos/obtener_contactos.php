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
    SELECT 
        id, 
        nombre, 
        iglesia, 
        email, 
        celular, 
        direccion, 
        ciudad, 
        code
    FROM contactos
    ORDER BY nombre ASC
";
$result = pg_query($conexion, $query);

if (!$result) {
    http_response_code(500); // Error del servidor
    echo json_encode(['error' => 'Error al obtener los contactos']);
    exit;
}

$contactos = [];
while ($row = pg_fetch_assoc($result)) {
    $contactos[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'iglesia' => $row['iglesia'],
        'email' => $row['email'],
        'celular' => $row['celular'],
        'direccion' => $row['direccion'],
        'ciudad' => $row['ciudad'],
        'code' => $row['code'] // Asegúrate de que este campo exista en la base de datos
    ];
}

echo json_encode(['success' => true, 'contactos' => $contactos]);

pg_close($conexion);