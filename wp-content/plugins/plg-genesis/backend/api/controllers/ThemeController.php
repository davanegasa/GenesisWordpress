<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/repositories/ThemeRepository.php';
require_once dirname(__FILE__, 3) . '/services/ThemeService.php';

class PlgGenesis_ThemeController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/theme', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_theme' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);
		register_rest_route('plg-genesis/v1', '/theme', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'put_theme' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);
		register_rest_route('plg-genesis/v1', '/theme', [
			'methods'             => 'DELETE',
			'callback'            => [ __CLASS__, 'delete_theme' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);
	}

	public static function get_theme($request) {
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$svc = new PlgGenesis_ThemeService(new PlgGenesis_ThemeRepository());
		$theme = $svc->get($office) ?: self::default_theme();
		return new WP_REST_Response([ 'success' => true, 'data' => $theme ], 200);
	}

	public static function put_theme($request) {
		$office = $request->get_param('office') ?: PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$payload = $request->get_json_params();
		$svc = new PlgGenesis_ThemeService(new PlgGenesis_ThemeRepository());
		$result = $svc->update($office, is_array($payload) ? $payload : []);
		if (is_wp_error($result)) { return self::error($result); }
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function delete_theme($request) {
		$office = $request->get_param('office') ?: PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) { return self::error($office); }
		$svc = new PlgGenesis_ThemeService(new PlgGenesis_ThemeRepository());
		$result = $svc->reset($office);
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	private static function default_theme() {
		return [
			'bg' => '#F7F7FB',
			'text' => '#111827',
			'accent' => '#1F2937',
			'sidebarBg' => '#111827',
			'sidebarText' => '#E5E7EB',
			'cardBg' => '#FFFFFF',
		];
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