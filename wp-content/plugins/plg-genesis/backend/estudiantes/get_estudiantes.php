<?php
// Incluir el archivo de conexión a PostgreSQL para Docker
include '../db_docker.php';

// Consulta para obtener todos los datos (ejemplo con estudiantes)
$query = "SELECT id, id_estudiante, nombre1, nombre2, apellido1, apellido2, email, celular, doc_identidad, estado_civil, escolaridad, ocupacion FROM estudiantes";
$resultado = pg_query($conexion, $query);

// Verificar que la consulta fue exitosa
if (!$resultado) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . pg_last_error($conexion)]);
    pg_close($conexion);
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