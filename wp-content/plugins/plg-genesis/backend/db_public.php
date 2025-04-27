<?php
// Configuración manual de la base de datos pública (solo lectura)
define('PUBLIC_DB_HOST', 'localhost');
define('PUBLIC_DB_NAME', 'emmaus_estudiantes');
define('PUBLIC_DB_USER', 'emmaus_public');
define('PUBLIC_DB_PASSWORD', 'kmpl6mb40qo2');

// Conectar a la base de datos pública
function conectar_a_base_de_datos_publica() {
    return pg_connect("host=" . PUBLIC_DB_HOST . 
                      " dbname=" . PUBLIC_DB_NAME . 
                      " user=" . PUBLIC_DB_USER . 
                      " password=" . PUBLIC_DB_PASSWORD . 
                      " options='--client_encoding=UTF8'");
}

// Crear la conexión
$conexion = conectar_a_base_de_datos_publica();

// Validar la conexión
if (!$conexion || (!is_resource($conexion) && !($conexion instanceof \PgSql\Connection))) {
    error_log("Error en la conexión pública a la base de datos: " . pg_last_error() . "\n", 3, __DIR__ . "/error_log.txt");
    die("Error en la conexión a la base de datos pública.");
}
?>