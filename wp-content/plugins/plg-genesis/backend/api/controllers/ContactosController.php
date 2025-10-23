<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
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
			'permission_callback' => plg_genesis_can('plg_view_contacts')
		]);

		// Endpoints por ID (deprecated - mantener temporalmente por compatibilidad)
		register_rest_route('plg-genesis/v1', '/contactos/id/(?P<id>[0-9]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'obtener' ],
			'permission_callback' => plg_genesis_can('plg_view_contacts')
		]);

		register_rest_route('plg-genesis/v1', '/contactos/id/(?P<id>[0-9]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'actualizar' ],
			'permission_callback' => plg_genesis_can('plg_edit_contacts')
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
		
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
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