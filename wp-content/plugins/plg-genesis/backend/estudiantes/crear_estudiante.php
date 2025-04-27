<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido. Use POST.']);
    exit;
}

// Obtener datos del request
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Solicitud inválida. No se recibieron datos.']);
    exit;
}

// Validar campos obligatorios
$requiredFields = ['nombre1', 'apellido1', 'id_contacto'];   //, 'doc_identidad', 'email', 'celular', 'ciudad', 'iglesia',
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(['error' => "El campo $field es obligatorio."]);
        exit;
    }
}

// Generar o validar el estudiante Id
if (empty($data['estudiante_id'])) {
    // Obtener el código del contacto en lugar del ID
    $sqlCodigoContacto = "SELECT TRIM(code) AS code FROM contactos WHERE id = $1"; // Eliminar espacios adicionales
    $resultCodigoContacto = pg_query_params($conexion, $sqlCodigoContacto, [$data['id_contacto']]);

    if (!$resultCodigoContacto) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener el código del contacto: ' . pg_last_error($conexion)]);
        exit;
    }

    $rowContacto = pg_fetch_assoc($resultCodigoContacto);
    $codigoContacto = trim($rowContacto['code'] ?? ''); // Asegurarse de que no haya espacios

    if (empty($codigoContacto)) {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(['error' => 'El código del contacto no está disponible o es inválido.']);
        exit;
    }

    // Obtener el último ID de estudiantes
    $sqlUltimoId = "SELECT MAX(id) AS max_id FROM estudiantes";
    $resultUltimoId = pg_query($conexion, $sqlUltimoId);

    if (!$resultUltimoId) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener el último ID: ' . pg_last_error($conexion)]);
        exit;
    }

    $row = pg_fetch_assoc($resultUltimoId);
    $ultimoId = $row['max_id'] ? (int)$row['max_id'] : 0;

    // Generar nuevo ID dinámicamente según la oficina
    if ($oficina === 'BOG') {
        $codigoOficina = $codigoContacto; // Usar el código del contacto para Bogotá
    } else {
        $codigoOficina = ''; // En otras oficinas, no usar prefijo
    }

    $data['estudiante_id'] = $codigoOficina . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);
}

// Insertar el estudiante en la base de datos
$sqlInsertarEstudiante = "
    INSERT INTO estudiantes (
        id_estudiante, nombre1, nombre2, apellido1, apellido2, doc_identidad, email, celular, ciudad, iglesia, id_contacto
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11
    )
";
$params = [
    $data['estudiante_id'],
    $data['nombre1'],
    $data['nombre2'] ?? null,
    $data['apellido1'],
    $data['apellido2'] ?? null,
    $data['doc_identidad'],
    $data['email'],
    $data['celular'],
    $data['ciudad'],
    $data['iglesia'],
    $data['id_contacto']
];

$resultInsertar = pg_query_params($conexion, $sqlInsertarEstudiante, $params);

if (!$resultInsertar) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar el estudiante: ' . pg_last_error($conexion)]);
    exit;
}

// Respuesta exitosa
http_response_code(201);
echo json_encode(['success' => true, 'message' => 'Estudiante creado exitosamente', 'estudiante_id' => $data['estudiante_id']]);

// Cerrar conexión
pg_close($conexion);
?>