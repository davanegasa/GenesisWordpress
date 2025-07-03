<?php
// Incluir el archivo de conexión a la base de datos
include '../db.php'; // Ajusta la ruta según tu estructura

// Verificar la conexión
if (!$conexion) {
    error_log("Error de conexión: " . pg_last_error() . "\n", 3, __DIR__ . "/../error_log.txt");
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Consulta para obtener todos los datos (ejemplo con estudiantes)
$query = "SELECT id, id_estudiante, nombre1, nombre2, apellido1, apellido2, email, celular, doc_identidad, ciudad, iglesia, fecha_registro FROM estudiantes";
$resultado = pg_query($conexion, $query);

// Verificar si la consulta fue exitosa
if (!$resultado) {
    error_log("Error en la consulta: " . pg_last_error($conexion) . "\n", 3, __DIR__ . "/../error_log.txt");
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta']);
    exit;
}

// Convertir los resultados a JSON
$estudiantes = [];
while ($row = pg_fetch_assoc($resultado)) {
    $estudiantes[] = $row;
}

// Enviar la data en formato JSON al frontend
header('Content-Type: application/json');
echo json_encode($estudiantes);

// Cerrar la conexión
pg_close($conexion);
?>