<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 4) . '/infrastructure/ContactResolver.php';
require_once dirname(__FILE__, 3) . '/repositories/ContactosRepository.php';
require_once dirname(__FILE__, 3) . '/services/ContactosService.php';

class PlgGenesis_ContactosController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/contactos', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'search' ],
			'permission_callback' => plg_genesis_can('plg_view_contacts')
		]);

		register_rest_route('plg-genesis/v1', '/contactos', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'crear' ],
			'permission_callback' => plg_genesis_can('plg_create_contacts')
		]);

		// Endpoint por code (recomendado - identificador público)
		register_rest_route('plg-genesis/v1', '/contactos/(?P<code>[a-zA-Z0-9]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'obtener_por_code' ],
			'permission_callback' => plg_genesis_can('plg_view_contacts')
		]);

		register_rest_route('plg-genesis/v1', '/contactos/(?P<code>[a-zA-Z0-9]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'actualizar_por_code' ],
			'permission_callback' => plg_genesis_can('plg_edit_contacts')
		]);

		// Academic history endpoint
		register_rest_route('plg-genesis/v1', '/contactos/(?P<code>[a-zA-Z0-9\-_%]+)/academic-history', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_academic_history' ],
			'permission_callback' => function($request) {
				// Permitir si tiene el permiso general
				if (current_user_can('plg_view_contacts')) {
					return true;
				}
				// O si es contact_viewer - la validación del contacto específico se hace en el callback
				return PlgGenesis_ContactResolver::is_contact_viewer(get_current_user_id());
			}
		]);

		// Endpoints por ID (deprecated - mantener temporalmente por compatibilidad)
		register_rest_route('plg-genesis/v1', '/contactos/id/(?P<id>[0-9]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'obtener' ],
			'permission_callback' => function($request) {
				// Permitir si tiene el permiso general
				if (current_user_can('plg_view_contacts')) {
					return true;
				}
				// O si es contact_viewer accediendo a su propio contacto
				$contacto_id = intval($request->get_param('id'));
				$can_access = PlgGenesis_ContactResolver::can_access_contact(get_current_user_id(), $contacto_id);
				return !is_wp_error($can_access);
			}
		]);

		register_rest_route('plg-genesis/v1', '/contactos/id/(?P<id>[0-9]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'actualizar' ],
			'permission_callback' => plg_genesis_can('plg_edit_contacts')
		]);
		
		// Endpoints de gestión de acceso al portal
		register_rest_route('plg-genesis/v1', '/contactos/(?P<code>[a-zA-Z0-9]+)/crear-acceso', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'crear_acceso' ],
			'permission_callback' => plg_genesis_can('plg_create_users')
		]);
		
		register_rest_route('plg-genesis/v1', '/contactos/(?P<code>[a-zA-Z0-9]+)/acceso', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'obtener_acceso' ],
			'permission_callback' => plg_genesis_can('plg_view_users')
		]);
	}

	public static function search($request) {
		$q      = $request->get_param('q');
		$limit  = $request->get_param('limit');
		$offset = $request->get_param('offset');

		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }

		$repo = new PlgGenesis_ContactosRepository($conn);
		$svc  = new PlgGenesis_ContactosService($repo);
		$result = $svc->buscar($q, $limit, $offset);
		if (is_wp_error($result)) { return self::error($result); }

		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function crear($request) {
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$svc  = new PlgGenesis_ContactosService($repo);
		$result = $svc->crear(is_array($payload) ? $payload : []);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'created' => true, 'id' => $result ] ], 201);
	}

	public static function obtener($request) {
		$id = $request->get_param('id');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$svc  = new PlgGenesis_ContactosService($repo);
		$result = $svc->obtener($id);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function actualizar($request) {
		$id = $request->get_param('id');
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$svc  = new PlgGenesis_ContactosService($repo);
		$result = $svc->actualizar($id, is_array($payload) ? $payload : []);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'updated' => true ] ], 200);
	}

	public static function obtener_por_code($request) {
		$code = $request->get_param('code');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$result = $repo->getByCode($code);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function actualizar_por_code($request) {
		$code = $request->get_param('code');
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$result = $repo->updateByCode($code, is_array($payload) ? $payload : []);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'updated' => true ] ], 200);
	}

	public static function get_academic_history($request) {
		$code = $request->get_param('code');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		
		$repo = new PlgGenesis_ContactosRepository($conn);
		$result = $repo->getAcademicHistory($code);
		if (is_wp_error($result)) { return self::error($result); }
		
		// Validar permisos para contact_viewer
		$contacto_id = $result['id'] ?? null;
		if ($contacto_id) {
			$can_access = PlgGenesis_ContactResolver::can_access_contact(get_current_user_id(), $contacto_id);
			if (is_wp_error($can_access)) { return self::error($can_access); }
		}
		
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}
	
	public static function crear_acceso($request) {
		$code = $request->get_param('code');
		$payload = $request->get_json_params();
		$current_user_id = get_current_user_id();
		
		// Obtener contacto
		$office = PlgGenesis_OfficeResolver::resolve_user_office($current_user_id);
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$contacto = $repo->getByCode($code);
		if (is_wp_error($contacto)) { return self::error($contacto); }
		
		$contacto_id = $contacto['id'];
		
		// Verificar que NO tenga ya un usuario
		$existing = get_users(['meta_key' => 'contacto_id', 'meta_value' => $contacto_id]);
		if (!empty($existing)) {
			return self::error(new WP_Error('already_exists', 'Este contacto ya tiene acceso al portal', ['status' => 409]));
		}
		
		// Validar datos
		$username = sanitize_user($payload['username'] ?? '');
		$email = sanitize_email($payload['email'] ?? '');
		$password = $payload['password'] ?? '';
		
		if (empty($username) || empty($email) || empty($password)) {
			return self::error(new WP_Error('invalid_payload', 'Faltan datos requeridos', ['status' => 422]));
		}
		
		// Crear usuario en WordPress
		$user_id = wp_create_user($username, $password, $email);
		if (is_wp_error($user_id)) { return self::error($user_id); }
		
		// Vincular con contacto
		update_user_meta($user_id, 'contacto_id', $contacto_id);
		update_user_meta($user_id, 'oficina', $office);
		
		// Asignar rol
		$user = new WP_User($user_id);
		$user->set_role('plg_contact_viewer');
		
		// Actualizar nombre
		wp_update_user(['ID' => $user_id, 'display_name' => $contacto['nombre']]);
		
		// Enviar email (opcional)
		if (!empty($payload['enviar_email'])) {
			wp_new_user_notification($user_id, null, 'user');
		}
		
		return new WP_REST_Response(['success' => true, 'data' => ['user_id' => $user_id, 'username' => $username]], 201);
	}
	
	public static function obtener_acceso($request) {
		$code = $request->get_param('code');
		
		// Obtener contacto
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }
		$repo = new PlgGenesis_ContactosRepository($conn);
		$contacto = $repo->getByCode($code);
		if (is_wp_error($contacto)) { return self::error($contacto); }
		
		$contacto_id = $contacto['id'];
		
		// Buscar usuario vinculado
		$users = get_users(['meta_key' => 'contacto_id', 'meta_value' => $contacto_id]);
		
		if (empty($users)) {
			return new WP_REST_Response(['success' => true, 'data' => null], 200);
		}
		
		$user = $users[0];
		$data = [
			'user_id' => $user->ID,
			'username' => $user->user_login,
			'email' => $user->user_email,
			'created' => $user->user_registered,
		];
		
		return new WP_REST_Response(['success' => true, 'data' => $data], 200);
	}

	private static function error($wp_error) {
		$status = $wp_error->get_error_data()['status'] ?? 500;
		return new WP_REST_Response([
			'success' => false,
			'error'   => [
				'code'    => $wp_error->get_error_code(),
				'message' => $wp_error->get_error_message(),
				'details' => $wp_error->get_error_data(),
			]
		], $status);
	}
}