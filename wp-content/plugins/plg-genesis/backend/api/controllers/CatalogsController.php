<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_CatalogsController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/catalogs', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_catalogs' ],
			'permission_callback' => function() { return is_user_logged_in(); }
		]);
	}

	public static function get_catalogs($request) {
		$catalogs = [
			'civilStatus' => [ 'Soltero', 'Casado', 'Unión Libre', 'Divorciado', 'Viudo' ],
			'educationLevel' => [ 'Primaria', 'Secundaria', 'Técnico', 'Tecnólogo', 'Universitario', 'Postgrado', 'Ninguno' ],
		];
		return new WP_REST_Response([ 'success' => true, 'data' => $catalogs ], 200);
	}
}