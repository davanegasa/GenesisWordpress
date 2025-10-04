<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ConnectionProvider {
	public static function get_connection_for_office($office) {
		// Preferir variables de entorno tipo BOG_DB_HOST, etc.
		$host = getenv($office . '_DB_HOST');
		$db   = getenv($office . '_DB_NAME');
		$user = getenv($office . '_DB_USER');
		$pass = getenv($office . '_DB_PASSWORD');

		// Fallback: si estamos en Docker según WORDPRESS_DB_HOST, usar postgres default
		if (getenv('WORDPRESS_DB_HOST') === 'mariadb' && (!$host || !$db || !$user || !$pass)) {
			$host = 'postgres';
			$db   = 'emmaus_estudiantes';
			$user = 'emmaus_admin';
			$pass = 'emmaus1234+';
		}

		if (!$host || !$db || !$user || !$pass) {
			return new WP_Error('db_config_missing', 'Faltan credenciales de base de datos para la oficina', [ 'status' => 500 ]);
		}

		$conn = @pg_connect("host={$host} dbname={$db} user={$user} password={$pass} options='--client_encoding=UTF8'");
		if (!$conn || (!is_resource($conn) && !($conn instanceof \PgSql\Connection))) {
			return new WP_Error('db_connection_failed', 'No se pudo conectar a la base de datos', [ 'status' => 500 ]);
		}
		return $conn;
	}
}