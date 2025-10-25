<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 4) . '/infrastructure/ContactResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';

/**
 * UserController - Maneja endpoints relacionados con el usuario actual
 */
class PlgGenesis_UserController {
	
	/**
	 * Registra las rutas del controlador
	 */
	public static function register_routes() {
		// Endpoint para obtener información del usuario actual
		register_rest_route('plg-genesis/v1', '/me', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_me' ],
			'permission_callback' => '__return_true'  // Usuario autenticado (WP ya lo valida con nonce)
		]);
	}
	
	/**
	 * GET /me - Obtiene información del usuario actual
	 * 
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response con datos del usuario
	 */
	public static function get_me($request) {
		$user_id = get_current_user_id();
		
		if (!$user_id) {
			return new WP_REST_Response([
				'success' => false, 
				'error' => [
					'code' => 'not_logged_in', 
					'message' => 'No autenticado'
				]
			], 401);
		}
		
		$user = get_userdata($user_id);
		
		// Resolver contacto_id si es contact_viewer
		$contacto_id = PlgGenesis_ContactResolver::is_contact_viewer($user_id)
			? PlgGenesis_ContactResolver::resolve_user_contact($user_id)
			: null;
		
		// Construir respuesta
		$data = [
			'id' => $user_id,
			'name' => $user->display_name,
			'email' => $user->user_email,
			'roles' => $user->roles,
			'office' => get_user_meta($user_id, 'oficina', true) ?: null,
			'contacto_id' => is_wp_error($contacto_id) ? null : $contacto_id,
		];
		
		return new WP_REST_Response(['success' => true, 'data' => $data], 200);
	}
	
	/**
	 * Maneja errores de WP_Error convirtiéndolos a respuestas REST
	 * 
	 * @param WP_Error $wp_error El error de WordPress
	 * @return WP_REST_Response Response con el error formateado
	 */
	private static function error($wp_error) {
		$code = $wp_error->get_error_code();
		$message = $wp_error->get_error_message();
		$data = $wp_error->get_error_data();
		$status = is_array($data) && isset($data['status']) ? $data['status'] : 500;
		
		return new WP_REST_Response([
			'success' => false, 
			'error' => [
				'code' => $code, 
				'message' => $message
			]
		], $status);
	}
}

