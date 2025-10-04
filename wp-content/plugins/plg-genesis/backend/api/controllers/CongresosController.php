<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/CongresosRepository.php';
require_once dirname(__FILE__, 3) . '/services/CongresosService.php';

class PlgGenesis_CongresosController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/congresos', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_congresos' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_congreso' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);

		register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'put_congreso' ],
			'permission_callback' => function() { return current_user_can('edit_users'); }
		]);
	}

	public static function get_congresos($request) {
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) {
			return self::error($office);
		}
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }

		$repo = new PlgGenesis_CongresosRepository($conn);
		$svc  = new PlgGenesis_CongresosService($repo);
		$result = $svc->listar();
		if (is_wp_error($result)) { return self::error($result); }

		return new WP_REST_Response([
			'success' => true,
			'data'    => $result,
		], 200);
	}

	public static function get_congreso($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->obtener($request->get_param('id'));
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function put_congreso($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$payload = $request->get_json_params();
		if (!is_array($payload)) $payload = [];
		// Permitir actualizar solo estado sin necesidad de todos los campos
		$payload = array_intersect_key($payload, array_flip(['nombre','fecha','estado']));
		$result = $svc->actualizar($request->get_param('id'), is_array($payload)?$payload:[]);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'updated'=>true ] ], 200);
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