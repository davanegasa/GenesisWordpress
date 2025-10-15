<?php
if (!defined('ABSPATH')) { exit; }

// Cargar clases necesarias
require_once __DIR__ . '/setup/roles.php';
require_once __DIR__ . '/infrastructure/OfficeResolver.php';
require_once __DIR__ . '/infrastructure/ConnectionProvider.php';

/**
 * Hook de activación: setup inicial de roles y capabilities
 */
register_activation_hook(__FILE__, function() {
	PlgGenesis_Roles::setup_roles();
	flush_rewrite_rules();
});

/**
 * Hook de desactivación: limpiar roles
 */
register_deactivation_hook(__FILE__, function() {
	PlgGenesis_Roles::remove_roles();
	flush_rewrite_rules();
});

/**
 * Ejecutar setup de roles en cada carga (para actualizaciones)
 * Solo si hay cambios en la versión
 */
add_action('init', function() {
	$version = get_option('plg_genesis_roles_version', '0');
	$current_version = '1.0.0'; // Incrementar cuando cambien roles
	
	if (version_compare($version, $current_version, '<')) {
		PlgGenesis_Roles::setup_roles();
		update_option('plg_genesis_roles_version', $current_version);
	}
}, 5);

/**
 * Helper: valida cookie de WordPress y establece usuario actual.
 * Retorna true si el usuario está autenticado, false en caso contrario.
 */
function plg_genesis_validate_user_from_cookie() {
	$uid = 0;
	if (defined('LOGGED_IN_COOKIE') && isset($_COOKIE[LOGGED_IN_COOKIE])) {
		$uid = (int) wp_validate_auth_cookie('', 'logged_in');
		if ($uid) { 
			// Limpiar cache de usuario de WordPress
			wp_cache_delete($uid, 'users');
			wp_cache_delete($uid, 'user_meta');
			
			// Establecer usuario actual (esto recarga desde DB)
			wp_set_current_user($uid);
		}
	}
	if ($uid <= 0 && is_user_logged_in()) { 
		$uid = get_current_user_id();
		// Limpiar cache también para usuarios ya autenticados
		wp_cache_delete($uid, 'users');
		wp_cache_delete($uid, 'user_meta');
	}
	return ($uid > 0);
}

/**
 * Helper: verifica si el usuario tiene una capability específica
 * Valida la cookie primero y luego verifica el permiso
 */
function plg_genesis_user_can($capability) {
	plg_genesis_validate_user_from_cookie();
	return current_user_can($capability);
}

/**
 * Helper: permission_callback estándar para endpoints
 * Uso: 'permission_callback' => plg_genesis_can('plg_view_students')
 */
function plg_genesis_can($capability) {
	return function() use ($capability) {
		return plg_genesis_user_can($capability);
	};
}

add_action('rest_api_init', function () {
	register_rest_route('plg-genesis/v1', '/health', [
		'methods'  => 'GET',
		'callback' => function () {
			plg_genesis_validate_user_from_cookie();
			$login = is_user_logged_in();
			$uid = $login ? get_current_user_id() : 0;
			$office = null; $hasDb = false; $dbError = null;
			if ($login) {
				try {
					$office = PlgGenesis_OfficeResolver::resolve_user_office($uid);
					if (is_wp_error($office)) { $dbError = $office->get_error_message(); }
					else {
						$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
						$hasDb = !is_wp_error($conn) && $conn !== null;
						if (is_wp_error($conn)) { $dbError = $conn->get_error_message(); }
					}
				} catch (Exception $e) { $dbError = $e->getMessage(); }
			}
			// Debug info: roles y capabilities
			$user_info = [];
			if ($login && $uid > 0) {
				$user = wp_get_current_user();
				$user_info = [
					'roles' => $user->roles,
					'capabilities' => array_keys(array_filter($user->allcaps, function($v) { return $v === true; })),
				];
			}
			
			return new WP_REST_Response([
				'success' => true,
				'data' => [
					'status' => 'ok',
					'timestamp' => time(),
					'loggedIn' => (bool)$login,
					'userId' => $uid > 0 ? $uid : null,
					'office' => is_wp_error($office) ? null : $office,
					'hasDb' => (bool)$hasDb,
					'dbError' => $dbError,
					'userInfo' => $user_info,
				],
			], 200);
		},
		'permission_callback' => '__return_true',
	]);

    // Endpoint para obtener nonce de REST (autentica por cookie sin exigir nonce previo)
    register_rest_route('plg-genesis/v1', '/auth/nonce', [
        'methods'  => 'GET',
        'callback' => function () {
            // Intentar resolver usuario desde cookie 'logged_in' cuando la API REST aún no tiene nonce
            $uid = 0;
            if (defined('LOGGED_IN_COOKIE') && isset($_COOKIE[LOGGED_IN_COOKIE])) {
                $uid = (int) wp_validate_auth_cookie('', 'logged_in');
                if ($uid) { wp_set_current_user($uid); }
            }
            if ($uid <= 0 && is_user_logged_in()) { $uid = get_current_user_id(); }
            if ($uid <= 0) {
                return new WP_REST_Response([
                    'success' => false,
                    'error'   => [ 'code' => 'not_logged_in', 'message' => 'Usuario no autenticado' ]
                ], 401);
            }
            return [ 'success' => true, 'data' => [ 'nonce' => wp_create_nonce('wp_rest') ] ];
        },
        'permission_callback' => '__return_true',
    ]);

	// Endpoint para servir Swagger UI (solo usuarios autenticados)
	register_rest_route('plg-genesis/v1', '/docs', [
		'methods'  => 'GET',
		'callback' => function () {
			$html = file_get_contents(ABSPATH . 'docs/swagger.html');
			if (!$html) {
				return new WP_REST_Response(['error' => 'Swagger UI not found'], 404);
			}
			// Ajustar la ruta relativa del openapi.yaml para que apunte al endpoint correcto
			$html = str_replace("url: './openapi.yaml'", "url: '" . home_url('/wp-json/plg-genesis/v1/docs/openapi') . "'", $html);
			header('Content-Type: text/html; charset=utf-8');
			echo $html;
			exit;
		},
		'permission_callback' => function() { return is_user_logged_in(); },
	]);

	// Endpoint para servir el archivo openapi.yaml (solo usuarios autenticados)
	register_rest_route('plg-genesis/v1', '/docs/openapi', [
		'methods'  => 'GET',
		'callback' => function () {
			if (!is_user_logged_in()) {
				return new WP_REST_Response(['error' => 'Unauthorized'], 401);
			}
			// Ruta del openapi.yaml dentro del plugin
			$yaml_path = __DIR__ . '/../docs/openapi.yaml';
			if (!file_exists($yaml_path)) {
				return new WP_REST_Response(['error' => 'OpenAPI spec not found', 'path' => $yaml_path], 404);
			}
			$yaml = file_get_contents($yaml_path);
			// Devolver YAML directamente sin que REST API lo envuelva en JSON
			status_header(200);
			header('Content-Type: text/yaml; charset=utf-8');
			header('Content-Length: ' . strlen($yaml));
			echo $yaml;
			exit;
		},
		'permission_callback' => '__return_true', // Verificamos dentro del callback
	]);

	// Registrar controladores API
	require_once __DIR__ . '/api/controllers/EstudiantesController.php';
	if (class_exists('PlgGenesis_EstudiantesController')) {
		PlgGenesis_EstudiantesController::register_routes();
	}
    require_once __DIR__ . '/api/controllers/AuthController.php';
    if (class_exists('PlgGenesis_AuthController')) {
        PlgGenesis_AuthController::register_routes();
    }
	require_once __DIR__ . '/api/controllers/UsersController.php';
	if (class_exists('PlgGenesis_UsersController')) {
		PlgGenesis_UsersController::register_routes();
	}
	// Controlador temporal de migración (eliminar después de migrar)
	// NOTA: MigrationController deshabilitado tras migración inicial
	// Descomentar solo si necesitas acceder a la UI de migración de nuevo
	// require_once __DIR__ . '/api/controllers/MigrationController.php';
	// if (class_exists('PlgGenesis_MigrationController')) {
	// 	PlgGenesis_MigrationController::register_routes();
	// }
	require_once __DIR__ . '/api/controllers/CongresosController.php';
	if (class_exists('PlgGenesis_CongresosController')) {
		PlgGenesis_CongresosController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/EstadisticasController.php';
	if (class_exists('PlgGenesis_EstadisticasController')) {
		PlgGenesis_EstadisticasController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/ContactosController.php';
	if (class_exists('PlgGenesis_ContactosController')) {
		PlgGenesis_ContactosController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/ThemeController.php';
	if (class_exists('PlgGenesis_ThemeController')) {
		PlgGenesis_ThemeController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/CatalogsController.php';
	if (class_exists('PlgGenesis_CatalogsController')) {
		PlgGenesis_CatalogsController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/ProgramasController.php';
	if (class_exists('PlgGenesis_ProgramasController')) {
		PlgGenesis_ProgramasController::register_routes();
	}
	require_once __DIR__ . '/api/controllers/CoursesController.php';
	if (class_exists('PlgGenesis_CoursesController')) {
		PlgGenesis_CoursesController::register_routes();
	}
    
    // CORS controlado (whitelist): permitir dashboard desde dominios específicos
    add_action('rest_api_init', function(){
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value){
            $origin = get_http_origin();
            $allowed = [
                'https://emmausbogota.com',
                'https://emmausdigital.com',
            ];
            if ($origin && in_array($origin, $allowed, true)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
                header('Vary: Origin');
                header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            }
            if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
                status_header(200);
                return true;
            }
            return $value;
        }, 15);
    });

    // Ajustar SameSite para permitir credenciales cross-site SOLO sobre HTTPS
    if (!function_exists('plg_genesis_cookie_samesite')) {
        function plg_genesis_cookie_samesite($samesite){
            if (is_ssl()) return 'None';
            return $samesite; // mantiene valor por defecto (Lax) si no es HTTPS
        }
        add_filter('wp_cookie_samesite', 'plg_genesis_cookie_samesite');
    }
});