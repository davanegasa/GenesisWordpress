<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/EstadisticasRepository.php';
require_once dirname(__FILE__, 3) . '/services/EstadisticasService.php';

class PlgGenesis_EstadisticasController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/estadisticas', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_resumen' ],
			'permission_callback' => plg_genesis_can('plg_view_stats')
		]);

		register_rest_route('plg-genesis/v1', '/estadisticas/informe-anual', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_informe_anual' ],
			'permission_callback' => plg_genesis_can('plg_view_stats')
		]);
	}

	public static function get_resumen($request) {
		$month = $request->get_param('month');
		$year  = $request->get_param('year');

		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }

		$repo = new PlgGenesis_EstadisticasRepository($conn);
		$svc  = new PlgGenesis_EstadisticasService($repo);
		$result = $svc->resumen($month, $year);
		if (is_wp_error($result)) { return self::error($result); }

		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function get_informe_anual($request) {
		$year = $request->get_param('year');

		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }

		$repo = new PlgGenesis_EstadisticasRepository($conn);
		$svc  = new PlgGenesis_EstadisticasService($repo);
		$result = $svc->informeAnual($year);
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