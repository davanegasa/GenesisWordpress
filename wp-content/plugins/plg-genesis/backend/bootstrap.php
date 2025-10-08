<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
	register_rest_route('plg-genesis/v1', '/health', [
		'methods'  => 'GET',
		'callback' => function () {
			return [
				'success' => true,
				'data' => [
					'status' => 'ok',
					'timestamp' => time(),
				],
			];
		},
		'permission_callback' => '__return_true',
	]);

	// Endpoint para obtener nonce de REST (requiere sesión)
	register_rest_route('plg-genesis/v1', '/auth/nonce', [
		'methods'  => 'GET',
		'callback' => function () {
			if (!is_user_logged_in()) {
				return new WP_REST_Response([
					'success' => false,
					'error'   => [ 'code' => 'not_logged_in', 'message' => 'Usuario no autenticado' ]
				], 401);
			}
			return [ 'success' => true, 'data' => [ 'nonce' => wp_create_nonce('wp_rest') ] ];
		},
		'permission_callback' => function () { return is_user_logged_in(); },
	]);

	// Registrar controladores API
	require_once __DIR__ . '/api/controllers/EstudiantesController.php';
	if (class_exists('PlgGenesis_EstudiantesController')) {
		PlgGenesis_EstudiantesController::register_routes();
	}
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
});