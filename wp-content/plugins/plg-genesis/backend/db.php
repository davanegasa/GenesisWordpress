<?php
// Cargar el entorno de WordPress
require_once(__DIR__ . '/../../../../wp-load.php');

// Verificar si el usuario está autenticado
if (!is_user_logged_in()) {
    http_response_code(401); // No autorizado
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener información del usuario actual
$current_user = wp_get_current_user();

// Función auxiliar para obtener la configuración de la base de datos
function get_db_config($prefix) {
    // Verificar si las constantes están definidas
    $const_defined = defined($prefix . '_DB_HOST') && 
                    defined($prefix . '_DB_NAME') && 
                    defined($prefix . '_DB_USER') && 
                    defined($prefix . '_DB_PASSWORD');

    // Si las constantes están definidas y no estamos en Docker, usar las constantes
    if ($const_defined && getenv('WORDPRESS_DB_HOST') !== 'mariadb') {
        return [
            'host' => constant($prefix . '_DB_HOST'),
            'dbname' => constant($prefix . '_DB_NAME'),
            'user' => constant($prefix . '_DB_USER'),
            'password' => constant($prefix . '_DB_PASSWORD')
        ];
    }
    
    // Si estamos en Docker, usar configuración específica de Docker
    if (getenv('WORDPRESS_DB_HOST') === 'mariadb') {
        return [
            'host' => 'postgres',
            'dbname' => 'emmaus_estudiantes',
            'user' => 'emmaus_admin',
            'password' => 'emmaus1234+'
        ];
    }
    
    // Si no hay constantes, usar variables de entorno
    return [
        'host' => getenv($prefix . '_DB_HOST'),
        'dbname' => getenv($prefix . '_DB_NAME'),
        'user' => getenv($prefix . '_DB_USER'),
        'password' => getenv($prefix . '_DB_PASSWORD')
    ];
}

// Definir la función para conectar a la base de datos según la oficina
function conectar_a_base_de_datos_oficina($oficina) {
    switch($oficina) {
        case 'BOG':
            $config = get_db_config('BOG');
            break;
        case 'PER':
            $config = get_db_config('PER');
            break;
        case 'BUC':
            $config = get_db_config('BUC');
            break;
        case 'BAR':
            $config = get_db_config('BAR');
            break;
        case 'FDL':
            $config = get_db_config('FDL');
            break;
        case 'PR':
            $config = get_db_config('PR');
            break;
        case 'BO':
            $config = get_db_config('BO');
            break;
        default: 
            die("Error en la conexión a la base de datos. OFFICE NOT FOUND");
    }

    // Verificar que tengamos todos los valores necesarios
    if (empty($config['host']) || empty($config['dbname']) || empty($config['user']) || empty($config['password'])) {
        error_log("Error: Faltan credenciales de base de datos para la oficina $oficina", 3, __DIR__ . "/error_log.txt");
        die("Error: Configuración de base de datos incompleta para la oficina $oficina");
    }
    
    return pg_connect(
        "host=" . $config['host'] . 
        " dbname=" . $config['dbname'] . 
        " user=" . $config['user'] . 
        " password=" . $config['password'] . 
        " options='--client_encoding=UTF8'"
    );
}

// Obtener la oficina desde el meta de usuario
$oficina = get_user_meta(get_current_user_id(), 'oficina', true);

// Verificar que la oficina esté configurada
if (!$oficina) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Oficina no configurada para el usuario actual']);
    exit;
}

// Establecer la conexión
$conexion = conectar_a_base_de_datos_oficina($oficina);

// Validar la conexión
if (!$conexion || (!is_resource($conexion) && !($conexion instanceof \PgSql\Connection))) {
    // Loguear el error si la conexión falla
    error_log("Error en la conexión a la base de datos para la oficina $oficina: " . pg_last_error() . "\n", 3, __DIR__ . "/error_log.txt");
    die("Error en la conexión a la base de datos.");
}

// Loguear usuario y oficina
error_log("Usuario: " . $current_user->user_login . " se conectará a la oficina: " . $oficina . "\n", 3, __DIR__ . "/usuario_oficina_log.txt");
?>