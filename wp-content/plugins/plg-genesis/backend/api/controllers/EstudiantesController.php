<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/EstudiantesRepository.php';
require_once dirname(__FILE__, 3) . '/services/EstudiantesService.php';

class PlgGenesis_EstudiantesController {
	public static function register_routes() {
		// Registrar rutas específicas ANTES de la ruta genérica con {id}
		register_rest_route('plg-genesis/v1', '/estudiantes/exists', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'exists_estudiante' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/next-code', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'next_code' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_estudiantes' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_estudiante' ],
			'permission_callback' => function() { return current_user_can('edit_users'); }
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_estudiante' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'put_estudiante' ],
			'permission_callback' => function() { return current_user_can('edit_users'); }
		]);
	}

	public static function get_estudiantes($request) {
		$contactoId = $request->get_param('contactoId');

		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) {
			return self::error($office);
		}

		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) {
			return self::error($conn);
		}

		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$result = $svc->listarPorContacto($contactoId);

		if (is_wp_error($result)) {
			return self::error($result);
		}

		return new WP_REST_Response([
			'success' => true,
			'data'    => $result,
		], 200);
	}

	public static function get_estudiante($request) {
		$id = $request->get_param('id');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$result = $svc->getById($id);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function post_estudiante($request) {
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
        $result = $svc->create(is_array($payload) ? $payload : []);
        if (is_wp_error($result)) return self::error($result);
        return new WP_REST_Response([ 'success' => true, 'data' => [ 'created' => true, 'idEstudiante' => $result ] ], 201);
	}

	public static function put_estudiante($request) {
		$id = $request->get_param('id');
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$result = $svc->update($id, is_array($payload) ? $payload : []);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'updated' => true ] ], 200);
	}

	public static function exists_estudiante($request) {
		$doc = $request->get_param('doc');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$svc  = new PlgGenesis_EstudiantesService(new PlgGenesis_EstudiantesRepository($conn));
		$result = $svc->existsByDocumento($doc);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'exists' => (bool)$result ] ], 200);
	}

	public static function next_code($request) {
		$contactId = $request->get_param('contactoId');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$svc  = new PlgGenesis_EstudiantesService(new PlgGenesis_EstudiantesRepository($conn));
		$result = $svc->nextCodeForContact($contactId);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'code' => $result ] ], 200);
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