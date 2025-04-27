<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar que se pase el ID del contacto
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de contacto no proporcionado']);
    exit;
}

$contactoId = intval($_GET['id']);

// Consultar los detalles del contacto
$queryContacto = "
    SELECT id, nombre, code, celular, email, direccion, ciudad, iglesia
    FROM contactos
    WHERE id = $1
";
$resultContacto = pg_query_params($conexion, $queryContacto, [$contactoId]);

if (!$resultContacto || pg_num_rows($resultContacto) === 0) {
    echo json_encode(['success' => false, 'error' => 'Contacto no encontrado']);
    exit;
}

$contacto = pg_fetch_assoc($resultContacto);

// Consultar los programas asignados
$queryProgramas = "
    SELECT p.id, p.nombre, p.descripcion 
    FROM programas_asignaciones pa
    JOIN programas p ON pa.programa_id = p.id
    WHERE pa.contacto_id = $1
";
$resultProgramas = pg_query_params($conexion, $queryProgramas, [$contactoId]);

$programas = [];
while ($row = pg_fetch_assoc($resultProgramas)) {
    $programas[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'descripcion' => $row['descripcion']
    ];
}
pg_free_result($resultProgramas);

pg_close($conexion);

// Retornar los datos como JSON
echo json_encode(['success' => true, 'contacto' => $contacto, 'programas' => $programas]);