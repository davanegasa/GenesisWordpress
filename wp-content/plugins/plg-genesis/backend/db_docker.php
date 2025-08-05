<?php
// Archivo de conexión simplificado para Docker
// Este archivo establece una conexión directa a PostgreSQL para el entorno de Docker

// Configuración de la base de datos PostgreSQL para Docker
$host = 'postgres';
$dbname = 'emmaus_estudiantes';
$user = 'emmaus_admin';
$password = 'emmaus1234+';

// Establecer la conexión
$conexion = pg_connect("host=$host dbname=$dbname user=$user password=$password options='--client_encoding=UTF8'");

// Verificar la conexión
if (!$conexion) {
    error_log("Error en la conexión a PostgreSQL: " . pg_last_error(), 3, __DIR__ . "/error_log.txt");
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . pg_last_error()]);
    exit;
}

// Log de conexión exitosa (opcional, para debugging)
error_log("Conexión exitosa a PostgreSQL en Docker", 3, __DIR__ . "/connection_log.txt");
?>