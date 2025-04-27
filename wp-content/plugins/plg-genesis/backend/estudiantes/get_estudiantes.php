<?php
// Incluir el archivo de conexi贸n a la base de datos
include '../db.php'; // Ajusta la ruta seg煤n tu estructura

// Consulta para obtener todos los datos (ejemplo con estudiantes)
$query = "SELECT id, id_estudiante, nombre1, nombre2, apellido1, apellido2, email, celular, doc_identidad FROM estudiantes";
$resultado = pg_query($conexion, $query);

// Convertir los resultados a JSON
$estudiantes = [];
while ($row = pg_fetch_assoc($resultado)) {
    $estudiantes[] = $row;
}

// Enviar la data en formato JSON al frontend
header('Content-Type: application/json');
echo json_encode($estudiantes);

// Cerrar la conexi贸n
pg_close($conexion);
?>